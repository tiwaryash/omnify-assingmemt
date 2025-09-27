<?php

namespace Database\Factories;

use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendeeFactory extends Factory
{
    protected $model = Attendee::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }

    /**
     * Create an attendee for a specific event
     */
    public function forEvent(Event $event): static
    {
        return $this->state(function (array $attributes) use ($event) {
            return [
                'event_id' => $event->id,
            ];
        });
    }
}
