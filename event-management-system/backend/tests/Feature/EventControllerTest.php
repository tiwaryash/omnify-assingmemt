<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_upcoming_events()
    {
        // Create upcoming events
        Event::factory()->count(15)->create([
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addHours(2),
        ]);

        // Create past events (should not be included)
        Event::factory()->count(5)->create([
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addHours(2),
        ]);

        $response = $this->getJson('/api/events?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'location',
                        'start_time',
                        'end_time',
                        'max_capacity',
                        'current_attendees',
                        'timezone'
                    ]
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'from',
                    'to'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Events retrieved successfully'
            ])
            ->assertJsonPath('pagination.per_page', 10)
            ->assertJsonPath('pagination.total', 15);
    }

    public function test_store_creates_new_event_with_valid_data()
    {
        $eventData = [
            'name' => 'Test Event',
            'location' => 'Test Location',
            'start_time' => Carbon::now()->addDays(1)->format('Y-m-d H:i:s'),
            'end_time' => Carbon::now()->addDays(1)->addHours(2)->format('Y-m-d H:i:s'),
            'max_capacity' => 100,
            'timezone' => 'Asia/Kolkata'
        ];

        $response = $this->postJson('/api/events', $eventData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'location',
                    'start_time',
                    'end_time',
                    'max_capacity',
                    'timezone'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Event created successfully',
                'data' => [
                    'name' => 'Test Event',
                    'location' => 'Test Location',
                    'max_capacity' => 100,
                    'timezone' => 'Asia/Kolkata'
                ]
            ]);

        $this->assertDatabaseHas('events', [
            'name' => 'Test Event',
            'location' => 'Test Location',
            'max_capacity' => 100
        ]);
    }

    public function test_store_fails_with_validation_errors()
    {
        $invalidData = [
            'name' => '', // Required field
            'location' => 'Test Location',
            'start_time' => 'invalid-date',
            'max_capacity' => 0 // Must be at least 1
        ];

        $response = $this->postJson('/api/events', $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'name',
                    'start_time',
                    'max_capacity'
                ]
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed'
            ]);
    }

    public function test_store_fails_with_past_start_time()
    {
        $eventData = [
            'name' => 'Test Event',
            'location' => 'Test Location',
            'start_time' => Carbon::now()->subHours(1)->format('Y-m-d H:i:s'),
            'end_time' => Carbon::now()->addHours(1)->format('Y-m-d H:i:s'),
            'max_capacity' => 100
        ];

        $response = $this->postJson('/api/events', $eventData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'start_time'
                ]
            ]);
    }

    public function test_show_returns_event_with_statistics()
    {
        $event = Event::factory()->create([
            'name' => 'Test Event',
            'max_capacity' => 100,
            'current_attendees' => 25,
            'start_time' => Carbon::now()->addDays(1)
        ]);

        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'event' => [
                        'id',
                        'name',
                        'location',
                        'start_time',
                        'end_time',
                        'max_capacity',
                        'current_attendees'
                    ],
                    'statistics' => [
                        'total_capacity',
                        'current_attendees',
                        'remaining_capacity',
                        'capacity_percentage',
                        'is_full',
                        'is_upcoming'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Event retrieved successfully',
                'data' => [
                    'event' => [
                        'name' => 'Test Event',
                        'max_capacity' => 100,
                        'current_attendees' => 25
                    ],
                    'statistics' => [
                        'total_capacity' => 100,
                        'current_attendees' => 25,
                        'remaining_capacity' => 75,
                        'capacity_percentage' => 25.0,
                        'is_full' => false,
                        'is_upcoming' => true
                    ]
                ]
            ]);
    }

    public function test_show_returns_404_for_nonexistent_event()
    {
        $response = $this->getJson('/api/events/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Event not found'
            ]);
    }

    public function test_update_modifies_existing_event()
    {
        $event = Event::factory()->create([
            'name' => 'Original Name',
            'location' => 'Original Location',
            'max_capacity' => 50
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'location' => 'Updated Location',
            'max_capacity' => 100
        ];

        $response = $this->putJson("/api/events/{$event->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Event updated successfully',
                'data' => [
                    'name' => 'Updated Name',
                    'location' => 'Updated Location',
                    'max_capacity' => 100
                ]
            ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => 'Updated Name',
            'location' => 'Updated Location',
            'max_capacity' => 100
        ]);
    }

    public function test_update_fails_with_validation_errors()
    {
        $event = Event::factory()->create();

        $invalidData = [
            'name' => '', // Required when present
            'max_capacity' => 0 // Must be at least 1
        ];

        $response = $this->putJson("/api/events/{$event->id}", $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ]);
    }

    public function test_update_returns_404_for_nonexistent_event()
    {
        $updateData = ['name' => 'Updated Name'];

        $response = $this->putJson('/api/events/999', $updateData);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Event not found'
            ]);
    }

    public function test_destroy_deletes_existing_event()
    {
        $event = Event::factory()->create(['name' => 'To Delete']);

        $response = $this->deleteJson("/api/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Event deleted successfully'
            ]);

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_event()
    {
        $response = $this->deleteJson('/api/events/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Event not found'
            ]);
    }

    public function test_index_handles_server_errors_gracefully()
    {
        // This test would require mocking to simulate server errors
        // For now, we'll test the basic functionality
        $response = $this->getJson('/api/events');
        
        $response->assertJsonStructure([
            'success',
            'message'
        ]);
    }

    public function test_store_with_timezone_conversion()
    {
        $eventData = [
            'name' => 'Timezone Test Event',
            'location' => 'Mumbai, India',
            'start_time' => '2025-12-01 15:30:00', // 3:30 PM IST
            'end_time' => '2025-12-01 17:30:00',   // 5:30 PM IST
            'max_capacity' => 50,
            'timezone' => 'Asia/Kolkata'
        ];

        $response = $this->postJson('/api/events', $eventData);

        $response->assertStatus(201);

        // Verify the event was created with correct timezone conversion
        $event = Event::where('name', 'Timezone Test Event')->first();
        $this->assertNotNull($event);
        
        // IST is UTC+5:30, so 15:30 IST should be stored as 10:00 UTC
        $this->assertEquals('10:00:00', $event->start_time->format('H:i:s'));
        $this->assertEquals('12:00:00', $event->end_time->format('H:i:s'));
    }

    public function test_partial_update_with_patch()
    {
        $event = Event::factory()->create([
            'name' => 'Original Name',
            'location' => 'Original Location',
            'max_capacity' => 50
        ]);

        // Only update the name
        $updateData = ['name' => 'Partially Updated Name'];

        $response = $this->patchJson("/api/events/{$event->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Partially Updated Name',
                    'location' => 'Original Location', // Should remain unchanged
                    'max_capacity' => 50 // Should remain unchanged
                ]
            ]);
    }
}
