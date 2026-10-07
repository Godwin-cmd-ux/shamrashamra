<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\Invitation;
use App\Models\Rsvp;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RsvpTest extends TestCase
{
    use RefreshDatabase;

    private Event $event;
    private Invitation $invitation;
    private string $token;
    private User $organizer;

    protected function setUp(): void
    {
        parent::setUp();

        $organizer = $this->organizer = User::factory()->create();
        $this->event = Event::factory()->published()->create([
            'organizer_id' => $organizer->id,
            'rsvp_deadline' => now()->addWeek(),
        ]);

        EventMember::create([
            'event_id' => $this->event->id,
            'user_id' => $organizer->id,
            'role' => 'owner',
            'status' => 'active',
        ]);

        [$this->invitation, $this->token] = app(InvitationService::class)->issue($this->event, [
            'label' => 'Family R',
            'entitlement_count' => 3,
        ], $organizer);
    }

    public function test_guest_can_confirm_within_entitlement_without_account(): void
    {
        $this->assertGuest();

        $this->post("/i/{$this->token}/rsvp", [
            'rsvp_status' => 'confirmed',
            'guest_count' => 2,
        ])->assertRedirect();

        $this->invitation->refresh();
        $this->assertSame('confirmed', $this->invitation->rsvp_status);
        $this->assertSame(2, $this->invitation->rsvp_guest_count);
        $this->assertNotNull($this->invitation->rsvp_responded_at);
        $this->assertDatabaseHas('rsvps', [
            'invitation_id' => $this->invitation->id,
            'status' => 'confirmed',
            'guest_count' => 2,
            'source' => 'link',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'guest.rsvp']);
    }

    public function test_decline_saves_without_headcount(): void
    {
        $this->post("/i/{$this->token}/rsvp", ['rsvp_status' => 'declined'])->assertRedirect();

        $this->invitation->refresh();
        $this->assertSame('declined', $this->invitation->rsvp_status);
        $this->assertNull($this->invitation->rsvp_guest_count);
    }

    public function test_guest_count_cannot_exceed_entitlement(): void
    {
        $this->post("/i/{$this->token}/rsvp", [
            'rsvp_status' => 'confirmed',
            'guest_count' => 10,
        ])->assertSessionHasErrors('guest_count');

        $this->assertSame('pending', $this->invitation->fresh()->rsvp_status);
    }

    public function test_event_capacity_is_enforced_server_side(): void
    {
        $this->event->update(['guest_capacity' => 2]);

        // Another invitation already fills capacity.
        app(InvitationService::class)->issue($this->event, ['label' => 'Other', 'entitlement_count' => 2], $this->organizer);
        $other = Invitation::where('id', '!=', $this->invitation->id)->first();
        $other->update(['rsvp_status' => 'confirmed', 'rsvp_guest_count' => 2]);

        $this->post("/i/{$this->token}/rsvp", [
            'rsvp_status' => 'confirmed',
            'guest_count' => 1,
        ])->assertSessionHasErrors('rsvp');

        $this->assertSame('pending', $this->invitation->fresh()->rsvp_status);
    }

    public function test_rsvp_rejected_after_deadline(): void
    {
        $this->event->update(['rsvp_deadline' => now()->subDay()]);

        $this->post("/i/{$this->token}/rsvp", [
            'rsvp_status' => 'confirmed',
            'guest_count' => 1,
        ])->assertSessionHasErrors('rsvp');

        $this->assertSame('pending', $this->invitation->fresh()->rsvp_status);
    }

    public function test_rsvp_rejected_for_revoked_invitation(): void
    {
        app(InvitationService::class)->revoke($this->invitation, User::factory()->create());

        $this->post("/i/{$this->token}/rsvp", ['rsvp_status' => 'confirmed', 'guest_count' => 1])
            ->assertStatus(410);
        $this->assertSame('pending', $this->invitation->fresh()->rsvp_status);
    }

    public function test_rsvp_rejected_when_event_not_published(): void
    {
        $this->event->update(['status' => 'draft']);

        $this->get("/i/{$this->token}")->assertNotFound();
        $this->post("/i/{$this->token}/rsvp", ['rsvp_status' => 'confirmed'])->assertNotFound();
    }

    public function test_invalid_rsvp_status_rejected(): void
    {
        $this->post("/i/{$this->token}/rsvp", ['rsvp_status' => 'maybe'])
            ->assertSessionHasErrors('rsvp_status');
    }

    public function test_responses_are_appended_to_history(): void
    {
        $this->post("/i/{$this->token}/rsvp", ['rsvp_status' => 'confirmed', 'guest_count' => 1]);
        $this->post("/i/{$this->token}/rsvp", ['rsvp_status' => 'declined']);

        $this->assertSame(2, Rsvp::where('invitation_id', $this->invitation->id)->count());
        $this->assertSame('declined', $this->invitation->fresh()->rsvp_status);
    }
}
