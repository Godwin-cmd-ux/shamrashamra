<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class GuestManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $organizer;
    private Event $event;

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
    }

    public function test_organizer_can_create_and_update_guest(): void
    {
        $this->actingAs($this->organizer)->post("/events/{$this->event->public_id}/guests", [
            'name' => 'Asha Mrisho',
            'phone' => '+255712345678',
            'category' => 'family',
        ])->assertRedirect('/events/'.$this->event->public_id.'/guests');

        $guest = $this->event->guests()->first();
        $this->assertSame('Asha Mrisho', $guest->name);

        $this->actingAs($this->organizer)->put("/events/{$this->event->public_id}/guests/{$guest->id}", [
            'name' => 'Asha Updated',
            'phone' => '+255712345678',
        ])->assertRedirect();

        $this->assertSame('Asha Updated', $guest->fresh()->name);
    }

    public function test_invalid_phone_rejected(): void
    {
        $this->actingAs($this->organizer)->post("/events/{$this->event->public_id}/guests", [
            'name' => 'Bad Phone',
            'phone' => 'not-a-phone',
        ])->assertSessionHasErrors('phone');
    }

    public function test_cross_event_guest_cannot_be_modified(): void
    {
        $otherOwner = User::factory()->create();
        $otherEvent = Event::factory()->create(['organizer_id' => $otherOwner->id]);
        $foreignGuest = $otherEvent->guests()->create(['name' => 'Foreign']);

        $this->actingAs($this->organizer)
            ->put("/events/{$this->event->public_id}/guests/{$foreignGuest->id}", ['name' => 'Hacked'])
            ->assertNotFound();

        $this->assertSame('Foreign', $foreignGuest->fresh()->name);
    }

    public function test_csv_import_validates_rows_and_reports_invalid(): void
    {
        $csv = implode("\n", [
            'name,phone,email,category',
            'Neema Juma,+255711111111,neema@example.com,family',
            ',+255722222222,,friends',
            'Bad Email,+255,not-an-email,friends',
            'Unknown Cat,+255733333333,,vip_pass',
        ]);

        $file = UploadedFile::fake()->createWithContent('guests.csv', $csv);

        $this->actingAs($this->organizer)
            ->post("/events/{$this->event->public_id}/guests/import", ['csv_file' => $file])
            ->assertRedirect();

        $this->assertSame(1, $this->event->guests()->count());

        $result = session('importResult');
        $this->assertNotNull($result);
        $this->assertSame(1, $result['imported']);
        $this->assertSame(4, $result['total']);
        $this->assertCount(3, $result['invalid']); // missing name, bad phone, unknown category
    }

    public function test_csv_import_skips_duplicates(): void
    {
        $this->event->guests()->create(['name' => 'Existing', 'phone' => '+255711111111']);

        $csv = "name,phone\nExisting,+255711111111\nNew Person,+255799999999";
        $file = UploadedFile::fake()->createWithContent('guests.csv', $csv);

        $this->actingAs($this->organizer)
            ->post("/events/{$this->event->public_id}/guests/import", ['csv_file' => $file]);

        $result = session('importResult');
        $this->assertSame(1, $result['imported']);
        $this->assertSame(1, $result['duplicates']);
        $this->assertSame(2, $this->event->guests()->count());
    }

    public function test_csv_import_missing_name_column_rejected(): void
    {
        $file = UploadedFile::fake()->createWithContent('guests.csv', "phone,email\n+255711111111,a@b.com");

        $this->actingAs($this->organizer)
            ->post("/events/{$this->event->public_id}/guests/import", ['csv_file' => $file])
            ->assertSessionHas('importResult');

        $this->assertSame(0, $this->event->guests()->count());
        $this->assertNotEmpty(session('importResult')['invalid']);
    }

    public function test_export_guards_against_formula_injection_and_requires_access(): void
    {
        $this->event->guests()->create(['name' => '=SUM(A1:A9)', 'phone' => '+255700000000']);

        $response = $this->actingAs($this->organizer)
            ->get("/events/{$this->event->public_id}/guests/export");

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString("'=SUM(A1:A9)", $content);
        $this->assertStringContainsString('name,phone,email,category', $content);

        $stranger = User::factory()->create();
        $this->actingAs($stranger)
            ->get("/events/{$this->event->public_id}/guests/export")
            ->assertForbidden();
    }

    public function test_guest_index_requires_membership(): void
    {
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get("/events/{$this->event->public_id}/guests")->assertForbidden();
        $this->actingAs($this->organizer)->get("/events/{$this->event->public_id}/guests")->assertOk();
    }
}
