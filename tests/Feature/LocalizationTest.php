<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_switch_persists_across_requests(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['organizer_id' => $user->id]);

        EventMember::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post('/locale/sw')
            ->assertRedirect();

        $this->assertSame('sw', session('locale'));

        // Preference persists: next request renders Kiswahili.
        $this->actingAs($user)
            ->get('/events')
            ->assertOk()
            ->assertSee('Matukio')
            ->assertDontSee('Events', false);
    }

    public function test_user_locale_preference_is_persisted_to_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/locale/sw');

        $this->assertSame('sw', $user->fresh()->locale);
    }

    public function test_locale_can_be_switched_back_to_english(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/locale/sw');
        $this->actingAs($user)->post('/locale/en');

        $this->actingAs($user)->get('/events')->assertSee('Events');
    }

    public function test_invalid_locale_returns_404(): void
    {
        $this->post('/locale/de')->assertNotFound();
    }

    public function test_validation_messages_are_translated_to_swahili(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/locale/sw');

        $this->actingAs($user)
            ->post('/events', ['timezone' => 'Africa/Dar_es_Salaam'])
            ->assertSessionHasErrors('title');

        $errors = session('errors')->all();
        $this->assertStringContainsString('Kichwa inahitajika.', implode(' ', $errors));
    }

    public function test_guest_facing_page_respects_locale(): void
    {
        $organizer = User::factory()->create();
        $event = Event::factory()->published()->create(['organizer_id' => $organizer->id]);

        [$invitation, $token] = app(\App\Services\InvitationService::class)->issue($event, [
            'label' => 'Family',
        ], $organizer);

        $this->post('/locale/sw');
        $this->flushSession();

        // Re-switch after flush (session cleared by flush) and view the page.
        $this->post('/locale/sw');
        $this->get("/i/{$token}")
            ->assertOk()
            ->assertSee('Umefikishwa mwaliko');
    }
}
