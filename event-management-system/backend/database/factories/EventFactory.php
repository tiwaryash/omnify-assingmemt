<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('+1 day', '+30 days');
        $endTime = (clone $startTime)->modify('+2 hours');

        return [
            'name' => $this->faker->sentence(3),
            'location' => $this->faker->city . ', ' . $this->faker->country,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'max_capacity' => $this->faker->numberBetween(10, 500),
            'current_attendees' => 0,
            'timezone' => $this->faker->randomElement([
                'Asia/Kolkata',
                'America/New_York',
                'Europe/London',
                'Asia/Tokyo',
                'Australia/Sydney'
            ]),
        ];
    }

    /**
     * Create an event that is at full capacity
     */
    public function full(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'current_attendees' => $attributes['max_capacity'],
            ];
        });
    }

    /**
     * Create a past event
     */
    public function past(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = $this->faker->dateTimeBetween('-30 days', '-1 day');
            $endTime = (clone $startTime)->modify('+2 hours');

            return [
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];
        });
    }

    /**
     * Create an upcoming event
     */
    public function upcoming(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = $this->faker->dateTimeBetween('+1 day', '+30 days');
            $endTime = (clone $startTime)->modify('+2 hours');

            return [
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];
        });
    }
}
