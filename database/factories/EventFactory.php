<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::uuid(),
            'organizer_id' => User::factory(),
            'title' => fake()->sentence(3),
            'status' => 'draft',
            'starts_at' => now()->addWeek(),
            'timezone' => 'Africa/Dar_es_Salaam',
            'venue_name' => fake()->company().' Hall',
            'venue_address' => fake()->city(),
            'description' => fake()->paragraph(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
