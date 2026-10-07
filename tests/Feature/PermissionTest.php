<?php

namespace Tests\Feature;

use App\Enums\EventPermission;
use App\Models\Event;
use App\Models\EventMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    private User $organizer;
    private Event $event;
    private User $committee;
    private User $attendant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizer = User::factory()->create();
        $this->event = Event::factory()->create(['organizer_id' => $this->organizer->id]);

        EventMember::create([
            'event_id' => $this->event->id,
            'user_id' => $this->organizer->id,
            'role' => 'owner',
            'status' => 'active',
        ]);

        // Committee member with ONLY guests.manage
        $this->committee = User::factory()->create();
        $member = EventMember::create([
            'event_id' => $this->event->id,
            'user_id' => $this->committee->id,
            'role' => 'committee',
            'status' => 'active',
        ]);
        $member->permissions()->create(['permission' => EventPermission::GuestsManage->value]);

        // Attendant (implicit check-in only)
        $this->attendant = User::factory()->create();
        EventMember::create([
            'event_id' => $this->event->id,
            'user_id' => $this->attendant->id,
            'role' => 'attendant',
            'status' => 'active',
        ]);
    }

    private function url(string $suffix = ''): string
    {
        return "/events/{$this->event->public_id}{$suffix}";
    }

    public function test_committee_member_gets_only_granted_permissions(): void
    {
        // Granted: guests.manage
        $this->actingAs($this->committee)->get($this->url('/guests'))->assertOk();

        // Denied: finance, invitations, members, reports, audit, check-in
        $this->actingAs($this->committee)->get($this->url('/finance/pledges'))->assertForbidden();
        $this->actingAs($this->committee)->get($this->url('/invitations'))->assertForbidden();
        $this->actingAs($this->committee)->get($this->url('/members'))->assertForbidden();
        $this->actingAs($this->committee)->get($this->url('/reports'))->assertForbidden();
        $this->actingAs($this->committee)->get($this->url('/audit'))->assertForbidden();
        $this->actingAs($this->committee)->get($this->url('/checkin'))->assertForbidden();
    }

    public function test_attendant_only_gets_checkin(): void
    {
        $this->actingAs($this->attendant)->get($this->url('/checkin'))->assertOk();

        $this->actingAs($this->attendant)->get($this->url('/guests'))->assertForbidden();
        $this->actingAs($this->attendant)->get($this->url('/invitations'))->assertForbidden();
        $this->actingAs($this->attendant)->get($this->url('/finance/pledges'))->assertForbidden();
        $this->actingAs($this->attendant)->get($this->url('/members'))->assertForbidden();
    }

    public function test_attendant_can_post_checkin_verify(): void
    {
        $this->actingAs($this->attendant)
            ->postJson($this->url('/checkin/verify'), ['token' => 'whatever'])
            ->assertOk()
            ->assertJson(['status' => 'invalid']);
    }

    public function test_revoked_membership_loses_all_access(): void
    {
        $member = EventMember::where('event_id', $this->event->id)
            ->where('user_id', $this->committee->id)
            ->first();
        $member->update(['status' => 'revoked']);

        $this->actingAs($this->committee)->get($this->url('/guests'))->assertForbidden();
        $this->actingAs($this->committee)->get($this->url('/checkin'))->assertForbidden();
    }

    public function test_organizer_has_full_access(): void
    {
        foreach (['', '/guests', '/invitations', '/finance/pledges', '/finance/contributions', '/finance/expenses', '/finance/budget', '/members', '/reports', '/audit', '/checkin', '/vendors'] as $path) {
            $this->actingAs($this->organizer)->get($this->url($path))->assertOk();
        }
    }

    public function test_inviting_member_requires_existing_account(): void
    {
        $this->actingAs($this->organizer)->post($this->url('/members'), [
            'email' => 'ghost@example.com',
            'role' => 'committee',
            'permissions' => ['guests.manage'],
        ])->assertSessionHasErrors('email');

        $this->assertSame(3, EventMember::where('event_id', $this->event->id)->count());
    }

    public function test_organizer_cannot_be_removed(): void
    {
        $ownerMember = EventMember::where('event_id', $this->event->id)
            ->where('user_id', $this->organizer->id)
            ->first();

        $this->actingAs($this->organizer)
            ->delete($this->url("/members/{$ownerMember->id}"))
            ->assertSessionHasErrors('member');
    }
}
