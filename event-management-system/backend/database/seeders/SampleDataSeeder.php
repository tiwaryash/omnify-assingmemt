<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Attendee;
use Carbon\Carbon;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample events
        $events = [
            [
                'name' => 'Tech Conference 2025',
                'location' => 'Mumbai, India',
                'start_time' => Carbon::now()->addDays(30)->setTime(10, 0),
                'end_time' => Carbon::now()->addDays(30)->setTime(18, 0),
                'max_capacity' => 100,
                'current_attendees' => 0,
                'timezone' => 'Asia/Kolkata'
            ],
            [
                'name' => 'Web Development Workshop',
                'location' => 'Delhi, India',
                'start_time' => Carbon::now()->addDays(15)->setTime(14, 0),
                'end_time' => Carbon::now()->addDays(15)->setTime(17, 0),
                'max_capacity' => 50,
                'current_attendees' => 0,
                'timezone' => 'Asia/Kolkata'
            ],
            [
                'name' => 'AI/ML Symposium',
                'location' => 'Bangalore, India',
                'start_time' => Carbon::now()->addDays(45)->setTime(9, 30),
                'end_time' => Carbon::now()->addDays(45)->setTime(16, 30),
                'max_capacity' => 200,
                'current_attendees' => 0,
                'timezone' => 'Asia/Kolkata'
            ],
            [
                'name' => 'Startup Pitch Event',
                'location' => 'Pune, India',
                'start_time' => Carbon::now()->addDays(7)->setTime(15, 0),
                'end_time' => Carbon::now()->addDays(7)->setTime(19, 0),
                'max_capacity' => 75,
                'current_attendees' => 0,
                'timezone' => 'Asia/Kolkata'
            ],
            [
                'name' => 'React.js Masterclass',
                'location' => 'Hyderabad, India',
                'start_time' => Carbon::now()->addDays(21)->setTime(11, 0),
                'end_time' => Carbon::now()->addDays(21)->setTime(16, 0),
                'max_capacity' => 30,
                'current_attendees' => 0,
                'timezone' => 'Asia/Kolkata'
            ]
        ];

        foreach ($events as $eventData) {
            Event::create($eventData);
        }

        // Create sample attendees for some events
        $attendees = [
            // Tech Conference 2025 (Event ID: 1)
            ['event_id' => 1, 'name' => 'John Doe', 'email' => 'john.doe@example.com'],
            ['event_id' => 1, 'name' => 'Jane Smith', 'email' => 'jane.smith@example.com'],
            ['event_id' => 1, 'name' => 'Mike Johnson', 'email' => 'mike.johnson@example.com'],
            ['event_id' => 1, 'name' => 'Sarah Wilson', 'email' => 'sarah.wilson@example.com'],
            ['event_id' => 1, 'name' => 'David Brown', 'email' => 'david.brown@example.com'],

            // Web Development Workshop (Event ID: 2)
            ['event_id' => 2, 'name' => 'Alice Cooper', 'email' => 'alice.cooper@example.com'],
            ['event_id' => 2, 'name' => 'Bob Miller', 'email' => 'bob.miller@example.com'],
            ['event_id' => 2, 'name' => 'Carol Davis', 'email' => 'carol.davis@example.com'],

            // AI/ML Symposium (Event ID: 3)
            ['event_id' => 3, 'name' => 'Emma Thompson', 'email' => 'emma.thompson@example.com'],
            ['event_id' => 3, 'name' => 'James Anderson', 'email' => 'james.anderson@example.com'],
            ['event_id' => 3, 'name' => 'Lisa Garcia', 'email' => 'lisa.garcia@example.com'],
            ['event_id' => 3, 'name' => 'Robert Taylor', 'email' => 'robert.taylor@example.com'],

            // Startup Pitch Event (Event ID: 4)
            ['event_id' => 4, 'name' => 'Kevin Lee', 'email' => 'kevin.lee@example.com'],
            ['event_id' => 4, 'name' => 'Michelle White', 'email' => 'michelle.white@example.com'],
        ];

        foreach ($attendees as $attendeeData) {
            Attendee::create($attendeeData);
            
            // Update event's current_attendees count
            $event = Event::find($attendeeData['event_id']);
            $event->increment('current_attendees');
        }

        $this->command->info('Sample data created successfully!');
        $this->command->info('Created ' . count($events) . ' events and ' . count($attendees) . ' attendees.');
    }
}
