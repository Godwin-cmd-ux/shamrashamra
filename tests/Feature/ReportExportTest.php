<?php

namespace Tests\Feature;

use App\Enums\EventPermission;
use App\Models\Event;
use App\Models\EventMember;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
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

        $this->event->guests()->create(['name' => 'Zawadi', 'phone' => '+255712345678', 'created_by' => $this->organizer->id]);
        [$invitation] = app(InvitationService::class)->issue($this->event, ['label' => 'Zawadi family'], $this->organizer);
        $invitation->update(['rsvp_status' => 'confirmed', 'rsvp_guest_count' => 2]);
        $this->event->pledges()->create(['contributor_name' => 'Zawadi', 'amount_minor' => 500000, 'recorded_by' => $this->organizer->id]);
        $this->event->contributions()->create(['amount_minor' => 250000, 'received_at' => now()->toDateString(), 'method' => 'cash', 'recorded_by' => $this->organizer->id]);
    }

    private function memberWith(array $permissions): User
    {
        $user = User::factory()->create();
        $member = EventMember::create([
            'event_id' => $this->event->id,
            'user_id' => $user->id,
            'role' => 'committee',
            'status' => 'active',
        ]);

        foreach ($permissions as $permission) {
            $member->permissions()->create(['permission' => $permission]);
        }

        return $user;
    }

    public function test_reports_page_lists_authorized_reports_for_owner(): void
    {
        $this->actingAs($this->organizer)
            ->get("/events/{$this->event->public_id}/reports")
            ->assertOk()
            ->assertSee(__('reports.guests'))
            ->assertSee(__('reports.contributions'));
    }

    public function test_guest_export_contains_data_and_csv_headers(): void
    {
        $response = $this->actingAs($this->organizer)
            ->get("/events/{$this->event->public_id}/reports/guests.csv");

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('Zawadi', $content);
        $this->assertStringContainsString('+255712345678', $content);
    }

    public function test_contribution_export_shows_minor_units_and_status(): void
    {
        $content = $this->actingAs($this->organizer)
            ->get("/events/{$this->event->public_id}/reports/contributions.csv")
            ->streamedContent();

        $this->assertStringContainsString('250000', $content);
        $this->assertStringContainsString('cash', $content);
        $this->assertStringContainsString('no', $content); // reversed = no
    }

    public function test_committee_with_reports_view_only_sees_non_finance_reports(): void
    {
        $user = $this->memberWith([EventPermission::ReportsView->value, EventPermission::GuestsView->value]);

        $this->actingAs($user)
            ->get("/events/{$this->event->public_id}/reports")
            ->assertOk()
            ->assertSee(__('reports.guests'))
            ->assertDontSee(__('reports.contributions'));

        // Explicit finance export attempt is forbidden.
        $this->actingAs($user)
            ->get("/events/{$this->event->public_id}/reports/contributions.csv")
            ->assertForbidden();

        // Guests export allowed via guests.view.
        $this->actingAs($user)
            ->get("/events/{$this->event->public_id}/reports/guests.csv")
            ->assertOk();
    }

    public function test_attendant_cannot_open_reports_page(): void
    {
        $attendant = User::factory()->create();
        EventMember::create([
            'event_id' => $this->event->id,
            'user_id' => $attendant->id,
            'role' => 'attendant',
            'status' => 'active',
        ]);

        $this->actingAs($attendant)
            ->get("/events/{$this->event->public_id}/reports")
            ->assertForbidden();
    }

    public function test_stranger_cannot_export_anything(): void
    {
        $stranger = User::factory()->create();

        $this->actingAs($stranger)
            ->get("/events/{$this->event->public_id}/reports/guests.csv")
            ->assertForbidden();
    }

    public function test_unknown_report_type_is_rejected(): void
    {
        $this->actingAs($this->organizer)
            ->get("/events/{$this->event->public_id}/reports/secrets.csv")
            ->assertNotFound();
    }
}
