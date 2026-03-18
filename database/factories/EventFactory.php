<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $totalSeats = $this->faker->numberBetween(50, 500);

        return [
            'title'           => $this->faker->sentence(4),
            'description'     => $this->faker->paragraphs(2, true),
            'location'        => $this->faker->city() . ', ' . $this->faker->country(),
            'event_datetime'  => $this->faker->dateTimeBetween('+1 week', '+6 months'),
            'total_seats'     => $totalSeats,
            'available_seats' => $this->faker->numberBetween((int) ($totalSeats * 0.3), $totalSeats),
            'created_by'      => User::factory(),
        ];
    }

    /**
     * Event in the past (for testing historical data).
     */
    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_datetime' => $this->faker->dateTimeBetween('-3 months', '-1 day'),
        ]);
    }

    /**
     * Fully sold-out event.
     */
    public function soldOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'available_seats' => 0,
        ]);
    }
}