<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Invitation;
use App\Services\InvitationService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invitation>
 */
class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    public function definition(): array
    {
        // Create a realistic invitation with a hashed token without exposing internals.
        $token = bin2hex(random_bytes(32));

        return [
            'event_id' => Event::factory(),
            'public_id' => (string) \Illuminate\Support\Str::uuid(),
            'label' => fake()->lastName().' Family',
            'entitlement_count' => 1,
            'status' => 'issued',
            'token_hash' => hash('sha256', $token),
            'token_encrypted' => $token,
        ];
    }

    public function configuredToken(): static
    {
        return $this->state(function () {
            $token = bin2hex(random_bytes(32));

            return [
                'token_hash' => hash('sha256', $token),
                'token_encrypted' => $token,
            ];
        });
    }
}
