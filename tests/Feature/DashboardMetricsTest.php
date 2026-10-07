<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\User;
use App\Services\InvitationService;
use App\Services\MetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_metrics_are_derived_from_database_state(): void
    {
        $organizer = User::factory()->create();

        $event = Event::factory()->published()->create([
            'organizer_id' => $organizer->id,
            'starts_at' => now()->addWeek(),
        ]);

        EventMember::create([
            'event_id' => $event->id,
            'user_id' => $organizer->id,
            'role' => 'owner',
            'status' => 'active',
        ]);

        $service = app(InvitationService::class);

        // Confirmed invitation, used for check-in.
        [$confirmed] = $service->issue($event, ['label' => 'Confirmed family', 'entitlement_count' => 2], $organizer);
        $confirmed->update(['rsvp_status' => 'confirmed', 'rsvp_guest_count' => 2]);

        // Pending invitation.
        [$pending] = $service->issue($event, ['label' => 'Pending family'], $organizer);

        // One valid check-in via the real service.
        app(\App\Services\CheckInService::class)->verify(
            $event,
            $confirmed->token_encrypted,
            $organizer,
            'qr',
            '127.0.0.1'
        );

        // Money: pledge 100,000 TZS pledged; 40,000 received; expense 25,000; budget 60,000.
        $event->pledges()->create(['contributor_name' => 'A', 'amount_minor' => 10000000, 'recorded_by' => $organizer->id]);
        $event->contributions()->create(['amount_minor' => 4000000, 'received_at' => now()->toDateString(), 'method' => 'cash', 'recorded_by' => $organizer->id]);
        $event->budgetCategories()->create(['name' => 'Decor', 'planned_amount_minor' => 6000000]);
        $event->expenses()->create(['description' => 'Flowers', 'amount_minor' => 2500000, 'incurred_at' => now()->toDateString(), 'recorded_by' => $organizer->id, 'approval_status' => 'not_required']);

        $metrics = app(MetricsService::class)->forUser($organizer);

        $this->assertSame(1, $metrics['active_events']);
        $this->assertSame(1, $metrics['upcoming_events']);
        $this->assertSame(1, $metrics['invited_guests']); // events that have issued invitations
        $this->assertSame(1, $metrics['rsvp_confirmed']);
        $this->assertSame(0, $metrics['rsvp_declined']);
        $this->assertSame(2, $metrics['invitations_issued']);
        $this->assertSame(1, $metrics['checkin_records']);
        $this->assertSame(2, $metrics['attendance_count']);

        // Pledges and contributions must stay separate.
        $this->assertSame(10000000, $metrics['total_pledged_minor']);
        $this->assertSame(4000000, $metrics['total_contributed_minor']);
        $this->assertSame(6000000, $metrics['outstanding_minor']);

        $this->assertSame(6000000, $metrics['planned_budget_minor']);
        $this->assertSame(2500000, $metrics['expenses_minor']);
        $this->assertSame(3500000, $metrics['remaining_budget_minor']);

        // The dashboard page renders these numbers.
        $this->actingAs($organizer)->get('/dashboard')->assertOk();
    }

    public function test_metrics_zero_for_user_without_events(): void
    {
        $user = User::factory()->create();

        $metrics = app(MetricsService::class)->forUser($user);

        $this->assertSame(0, $metrics['active_events']);
        $this->assertSame(0, $metrics['total_pledged_minor']);
        $this->assertSame(0, $metrics['attendance_count']);

        $this->actingAs($user)->get('/dashboard')->assertOk();
    }

    public function test_other_users_events_do_not_leak_into_metrics(): void
    {
        $mine = User::factory()->create();
        $theirs = User::factory()->create();

        $event = Event::factory()->published()->create(['organizer_id' => $theirs->id]);
        EventMember::create(['event_id' => $event->id, 'user_id' => $theirs->id, 'role' => 'owner', 'status' => 'active']);

        $event->pledges()->create(['contributor_name' => 'X', 'amount_minor' => 99900, 'recorded_by' => $theirs->id]);

        $metrics = app(MetricsService::class)->forUser($mine);

        $this->assertSame(0, $metrics['active_events']);
        $this->assertSame(0, $metrics['total_pledged_minor']);
    }
}
