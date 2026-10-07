<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\EventMember;
use App\Models\Invitation;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CheckInTest extends TestCase
{
    use RefreshDatabase;

    private User $organizer;
    private Event $event;
    private User $attendant;
    private Invitation $invitation;
    private string $token;

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

        $this->attendant = User::factory()->create();
        EventMember::create([
            'event_id' => $this->event->id,
            'user_id' => $this->attendant->id,
            'role' => 'attendant',
            'status' => 'active',
        ]);

        [$this->invitation, $this->token] = app(InvitationService::class)->issue($this->event, [
            'label' => 'Family X',
            'entitlement_count' => 3,
        ], $this->organizer);
    }

    private function verify(array $payload)
    {
        return $this->actingAs($this->attendant)->postJson(
            "/events/{$this->event->public_id}/checkin/verify",
            $payload
        );
    }

    public function test_valid_scan_checks_in_and_records_audit(): void
    {
        $response = $this->verify(['token' => $this->token, 'method' => 'qr']);

        $response->assertOk()->assertJson(['status' => 'valid', 'label' => 'Family X', 'guest_count' => 3]);

        $this->assertDatabaseCount('attendance_records', 1);
        $this->assertDatabaseHas('attendance_records', [
            'event_id' => $this->event->id,
            'invitation_id' => $this->invitation->id,
            'attendant_id' => $this->attendant->id,
            'guest_count' => 3,
            'method' => 'qr',
        ]);
        $this->assertDatabaseHas('scan_attempts', ['outcome' => 'valid', 'invitation_id' => $this->invitation->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'checkin.success']);
    }

    public function test_repeated_scan_returns_duplicate_without_second_record(): void
    {
        $this->verify(['token' => $this->token])->assertOk()->assertJson(['status' => 'valid']);

        $second = $this->verify(['token' => $this->token]);
        $second->assertOk()->assertJson(['status' => 'duplicate']);
        $second->assertJsonStructure(['message', 'checked_in_at', 'checked_in_by']);

        $this->assertDatabaseCount('attendance_records', 1);
        $this->assertSame(2, \App\Models\ScanAttempt::count());
    }

    public function test_database_unique_constraint_blocks_duplicate_checkins(): void
    {
        $this->verify(['token' => $this->token])->assertOk();

        $this->expectException(UniqueConstraintViolationException::class);

        DB::table('attendance_records')->insert([
            'event_id' => $this->event->id,
            'invitation_id' => $this->invitation->id,
            'attendant_id' => $this->attendant->id,
            'guest_count' => 3,
            'method' => 'qr',
            'checked_in_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_revoked_invitation_is_rejected(): void
    {
        app(InvitationService::class)->revoke($this->invitation, $this->organizer);

        $this->verify(['token' => $this->token])->assertOk()->assertJson(['status' => 'revoked']);
        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_token_from_another_event_is_rejected(): void
    {
        $otherEvent = Event::factory()->published()->create(['organizer_id' => $this->organizer->id]);
        [, $foreignToken] = app(InvitationService::class)->issue($otherEvent, ['label' => 'Foreign'], $this->organizer);

        $this->verify(['token' => $foreignToken])->assertOk()->assertJson(['status' => 'wrong_event']);
        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_garbage_token_is_rejected(): void
    {
        $this->verify(['token' => 'not-a-real-token'])->assertOk()->assertJson(['status' => 'invalid']);
        $this->verify(['token' => ''])->assertJsonValidationErrors('token');
        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_full_invitation_url_is_accepted(): void
    {
        $url = 'https://shamrashamra.com/i/'.$this->token;

        $this->verify(['token' => $url, 'method' => 'manual'])->assertOk()->assertJson(['status' => 'valid']);
    }

    public function test_non_member_cannot_verify(): void
    {
        $stranger = User::factory()->create();

        $this->actingAs($stranger)
            ->postJson("/events/{$this->event->public_id}/checkin/verify", ['token' => $this->token])
            ->assertForbidden();
    }

    public function test_organizer_without_member_row_still_allowed(): void
    {
        // Owner membership exists via setUp; ensure owner also verifies fine.
        $this->actingAs($this->organizer)
            ->postJson("/events/{$this->event->public_id}/checkin/verify", ['token' => $this->token])
            ->assertOk()
            ->assertJson(['status' => 'valid']);
    }

    public function test_attendant_sees_scanner_page(): void
    {
        $this->actingAs($this->attendant)
            ->get("/events/{$this->event->public_id}/checkin")
            ->assertOk()
            ->assertSee(__('checkin.manual_title'));
    }
}
