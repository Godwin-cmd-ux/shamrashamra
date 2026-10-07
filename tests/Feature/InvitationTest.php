<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    private User $organizer;
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizer = User::factory()->create();
        $this->event = Event::factory()->published()->create(['organizer_id' => $this->organizer->id]);

        EventMember::create([
            'event_id' => $this->event->id,
            'user_id' => $this->organizer->id,
            'role' => 'owner',
            'status' => 'active',
        ]);
    }

    public function test_issuing_stores_only_a_hash_of_the_token(): void
    {
        [$invitation, $token] = app(InvitationService::class)->issue($this->event, [
            'label' => 'The Mwangi Family',
            'entitlement_count' => 4,
        ], $this->organizer);

        $raw = DB::table('invitations')->where('id', $invitation->id)->first();

        $this->assertSame(hash('sha256', $token), $raw->token_hash);
        $this->assertNotSame($token, $raw->token_hash);
        $this->assertNotSame($token, $raw->token_encrypted, 'raw token must not be stored in plaintext');
        $this->assertSame(64, strlen($token), 'token must be 256-bit');
        $this->assertSame(4, (int) $raw->entitlement_count);
        $this->assertDatabaseHas('audit_logs', ['action' => 'invitation.issued', 'event_id' => $this->event->id]);
    }

    public function test_public_invitation_page_renders_for_issued_token_of_published_event(): void
    {
        [$invitation, $token] = app(InvitationService::class)->issue($this->event, [
            'label' => 'Asha family',
        ], $this->organizer);

        $this->get("/i/{$token}")
            ->assertOk()
            ->assertSee($this->event->title);
    }

    public function test_unknown_token_returns_404_page(): void
    {
        $this->get('/i/'.bin2hex(random_bytes(32)))->assertNotFound();
    }

    public function test_revoked_invitation_no_longer_authorizes_entry(): void
    {
        [$invitation, $token] = app(InvitationService::class)->issue($this->event, ['label' => 'X'], $this->organizer);

        $this->actingAs($this->organizer)
            ->post("/events/{$this->event->public_id}/invitations/{$invitation->id}/revoke")
            ->assertRedirect();

        $this->assertSame('revoked', $invitation->fresh()->status);
        $this->get("/i/{$token}")->assertStatus(410);
        $this->assertDatabaseHas('audit_logs', ['action' => 'invitation.revoked']);
    }

    public function test_replacing_invalidates_old_token_and_issues_working_new_one(): void
    {
        [$old, $oldToken] = app(InvitationService::class)->issue($this->event, ['label' => 'Y'], $this->organizer);

        $this->actingAs($this->organizer)
            ->post("/events/{$this->event->public_id}/invitations/{$old->id}/replace")
            ->assertRedirect();

        $old->refresh();
        $this->assertSame('replaced', $old->status);
        $this->assertNotNull($old->replaced_by_id);

        $this->get("/i/{$oldToken}")->assertStatus(410);

        $new = \App\Models\Invitation::find($old->replaced_by_id);
        $newToken = $new->token_encrypted;
        $this->get("/i/{$newToken}")->assertOk();
    }

    public function test_qr_endpoint_requires_invitation_permission_and_serves_png(): void
    {
        [$invitation] = app(InvitationService::class)->issue($this->event, ['label' => 'Z'], $this->organizer);

        $this->actingAs($this->organizer)
            ->get("/events/{$this->event->public_id}/invitations/{$invitation->id}/qr")
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');

        $stranger = User::factory()->create();
        $this->actingAs($stranger)
            ->get("/events/{$this->event->public_id}/invitations/{$invitation->id}/qr")
            ->assertForbidden();
    }

    public function test_attendant_cannot_access_invitation_management(): void
    {
        $attendant = User::factory()->create();
        EventMember::create([
            'event_id' => $this->event->id,
            'user_id' => $attendant->id,
            'role' => 'attendant',
            'status' => 'active',
        ]);

        $this->actingAs($attendant)->get("/events/{$this->event->public_id}/invitations")->assertForbidden();
        $this->actingAs($attendant)->get("/events/{$this->event->public_id}/guests")->assertForbidden();
    }

    public function test_invitation_requires_guest_or_label(): void
    {
        $this->actingAs($this->organizer)
            ->post("/events/{$this->event->public_id}/invitations", ['entitlement_count' => 1])
            ->assertSessionHasErrors('label');
    }
}
