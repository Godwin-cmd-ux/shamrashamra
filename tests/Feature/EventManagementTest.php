<?php

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    private function organizer(): User
    {
        return User::factory()->create();
    }

    private function eventFor(User $organizer, array $attrs = []): Event
    {
        $event = Event::factory()->create($attrs + ['organizer_id' => $organizer->id]);

        EventMember::create([
            'event_id' => $event->id,
            'user_id' => $organizer->id,
            'role' => 'owner',
            'status' => 'active',
        ]);

        return $event;
    }

    public function test_organizer_can_create_draft_event_with_owner_membership(): void
    {
        $user = $this->organizer();

        $response = $this->actingAs($user)->post('/events', [
            'title' => 'Neema & Juma Wedding',
            'timezone' => 'Africa/Dar_es_Salaam',
            'starts_at' => now()->addMonth()->format('Y-m-d\TH:i'),
            'venue_name' => 'Kilimanjaro Hall',
        ]);

        $event = Event::first();

        $this->assertNotNull($event);
        $response->assertRedirect(route('events.show', $event));
        $this->assertSame('draft', $event->status);
        $this->assertSame($user->id, $event->organizer_id);
        $this->assertDatabaseHas('event_members', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'event.created', 'event_id' => $event->id]);
    }

    public function test_status_must_move_through_valid_transitions_only(): void
    {
        $user = $this->organizer();
        $event = $this->eventFor($user);

        // Invalid: draft -> completed
        $this->actingAs($user)
            ->patch("/events/{$event->public_id}/status", ['status' => 'completed'])
            ->assertSessionHasErrors('status');
        $this->assertSame('draft', $event->fresh()->status);

        // Valid: draft -> published
        $this->actingAs($user)
            ->patch("/events/{$event->public_id}/status", ['status' => 'published'])
            ->assertSessionHasNoErrors();
        $this->assertSame('published', $event->fresh()->status);
        $this->assertNotNull($event->fresh()->published_at);

        // Invalid jump: published -> draft
        $this->actingAs($user)
            ->patch("/events/{$event->public_id}/status", ['status' => 'draft'])
            ->assertSessionHasErrors('status');
        $this->assertSame('published', $event->fresh()->status);
    }

    public function test_invalid_status_value_is_rejected(): void
    {
        $user = $this->organizer();
        $event = $this->eventFor($user);

        $this->actingAs($user)
            ->patch("/events/{$event->public_id}/status", ['status' => 'hacked'])
            ->assertSessionHasErrors('status');
    }

    public function test_non_member_cannot_view_or_edit_event(): void
    {
        $owner = $this->organizer();
        $intruder = $this->organizer();
        $event = $this->eventFor($owner);

        $this->actingAs($intruder)->get("/events/{$event->public_id}")->assertForbidden();
        $this->actingAs($intruder)->get("/events/{$event->public_id}/edit")->assertForbidden();
        $this->actingAs($intruder)->put("/events/{$event->public_id}", ['title' => 'Stolen', 'timezone' => 'Africa/Dar_es_Salaam'])->assertForbidden();
        $this->assertSame($event->title, $event->fresh()->title);
    }

    public function test_guest_sees_login_redirect_for_events(): void
    {
        $owner = $this->organizer();
        $event = $this->eventFor($owner);

        $this->get("/events/{$event->public_id}")->assertRedirect('/login');
    }

    public function test_empty_draft_can_be_deleted_but_draft_with_guests_cannot(): void
    {
        $user = $this->organizer();
        $empty = $this->eventFor($user, ['title' => 'Empty draft']);
        $withGuest = $this->eventFor($user, ['title' => 'Has guest']);

        $withGuest->guests()->create(['name' => 'Asha', 'created_by' => $user->id]);

        $this->actingAs($user)->delete("/events/{$withGuest->public_id}")->assertSessionHasErrors('event');
        $this->assertNotNull(Event::find($withGuest->id));

        $this->actingAs($user)->delete("/events/{$empty->public_id}")->assertRedirect('/events');
        $this->assertNull(Event::find($empty->id));
    }

    public function test_organizer_dashboard_lists_their_events_only(): void
    {
        $mine = $this->organizer();
        $other = $this->organizer();

        $this->eventFor($mine, ['title' => 'My Event Title']);
        $this->eventFor($other, ['title' => 'Someone Elses Event']);

        $this->actingAs($mine)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('My Event Title')
            ->assertDontSee('Someone Elses Event');
    }
}
