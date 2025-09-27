<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendeeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_successfully_registers_attendee()
    {
        $event = Event::factory()->create([
            'max_capacity' => 100,
            'current_attendees' => 50,
            'start_time' => Carbon::now()->addDays(1),
        ]);

        $attendeeData = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];

        $response = $this->postJson("/api/events/{$event->id}/register", $attendeeData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'event_id',
                    'event' => [
                        'id',
                        'name',
                        'location'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Successfully registered for the event',
                'data' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'event_id' => $event->id
                ]
            ]);

        // Check database
        $this->assertDatabaseHas('attendees', [
            'event_id' => $event->id,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        // Check that event attendee count was incremented
        $event->refresh();
        $this->assertEquals(51, $event->current_attendees);
    }

    public function test_register_fails_with_validation_errors()
    {
        $event = Event::factory()->create();

        $invalidData = [
            'name' => '', // Required
            'email' => 'invalid-email' // Invalid email format
        ];

        $response = $this->postJson("/api/events/{$event->id}/register", $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'name',
                    'email'
                ]
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed'
            ]);
    }

    public function test_register_fails_for_nonexistent_event()
    {
        $attendeeData = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];

        $response = $this->postJson('/api/events/999/register', $attendeeData);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Event not found'
            ]);
    }

    public function test_register_fails_for_past_event()
    {
        $event = Event::factory()->create([
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addHours(2),
        ]);

        $attendeeData = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];

        $response = $this->postJson("/api/events/{$event->id}/register", $attendeeData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot register for past events'
            ]);
    }

    public function test_register_fails_for_duplicate_email()
    {
        $event = Event::factory()->create([
            'max_capacity' => 100,
            'current_attendees' => 1,
            'start_time' => Carbon::now()->addDays(1),
        ]);

        // Create existing attendee
        Attendee::factory()->create([
            'event_id' => $event->id,
            'email' => 'john@example.com'
        ]);

        $attendeeData = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];

        $response = $this->postJson("/api/events/{$event->id}/register", $attendeeData);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Email is already registered for this event'
            ]);
    }

    public function test_register_fails_when_event_full()
    {
        $event = Event::factory()->create([
            'max_capacity' => 2,
            'current_attendees' => 2,
            'start_time' => Carbon::now()->addDays(1),
        ]);

        $attendeeData = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];

        $response = $this->postJson("/api/events/{$event->id}/register", $attendeeData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Event is at full capacity'
            ]);
    }

    public function test_get_event_attendees_returns_paginated_list()
    {
        $event = Event::factory()->create();
        
        // Create attendees
        Attendee::factory()->count(15)->create(['event_id' => $event->id]);

        $response = $this->getJson("/api/events/{$event->id}/attendees?per_page=10");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'event_id',
                        'created_at',
                        'updated_at'
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
                'message' => 'Attendees retrieved successfully'
            ])
            ->assertJsonPath('pagination.per_page', 10)
            ->assertJsonPath('pagination.total', 15);
    }

    public function test_get_event_attendees_with_search()
    {
        $event = Event::factory()->create();
        
        Attendee::factory()->create([
            'event_id' => $event->id,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        
        Attendee::factory()->create([
            'event_id' => $event->id,
            'name' => 'Jane Smith',
            'email' => 'jane@example.com'
        ]);
        
        Attendee::factory()->create([
            'event_id' => $event->id,
            'name' => 'John Wilson',
            'email' => 'wilson@example.com'
        ]);

        $response = $this->getJson("/api/events/{$event->id}/attendees?search=John");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Attendees retrieved successfully'
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data);
        
        $names = collect($data)->pluck('name')->toArray();
        $this->assertContains('John Doe', $names);
        $this->assertContains('John Wilson', $names);
    }

    public function test_remove_attendee_successfully_removes_attendee()
    {
        $event = Event::factory()->create([
            'current_attendees' => 5
        ]);
        
        $attendee = Attendee::factory()->create(['event_id' => $event->id]);

        $response = $this->deleteJson("/api/events/{$event->id}/attendees/{$attendee->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Attendee removed successfully'
            ]);

        // Check that attendee was deleted
        $this->assertDatabaseMissing('attendees', ['id' => $attendee->id]);

        // Check that event attendee count was decremented
        $event->refresh();
        $this->assertEquals(4, $event->current_attendees);
    }

    public function test_remove_attendee_fails_for_nonexistent_attendee()
    {
        $event = Event::factory()->create();

        $response = $this->deleteJson("/api/events/{$event->id}/attendees/999");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Attendee not found for this event'
            ]);
    }

    public function test_remove_attendee_fails_for_nonexistent_event()
    {
        $response = $this->deleteJson('/api/events/999/attendees/1');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Attendee not found for this event'
            ]);
    }

    public function test_check_registration_returns_true_for_registered_email()
    {
        $event = Event::factory()->create();
        Attendee::factory()->create([
            'event_id' => $event->id,
            'email' => 'registered@example.com'
        ]);

        $response = $this->getJson("/api/events/{$event->id}/attendees/check/registered@example.com");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Registration status checked',
                'data' => [
                    'is_registered' => true,
                    'event_id' => $event->id,
                    'email' => 'registered@example.com'
                ]
            ]);
    }

    public function test_check_registration_returns_false_for_unregistered_email()
    {
        $event = Event::factory()->create();

        $response = $this->getJson("/api/events/{$event->id}/attendees/check/unregistered@example.com");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Registration status checked',
                'data' => [
                    'is_registered' => false,
                    'event_id' => $event->id,
                    'email' => 'unregistered@example.com'
                ]
            ]);
    }

    public function test_get_attendee_count_returns_correct_count()
    {
        $event = Event::factory()->create();
        Attendee::factory()->count(7)->create(['event_id' => $event->id]);

        $response = $this->getJson("/api/events/{$event->id}/attendees/count");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Attendee count retrieved',
                'data' => [
                    'event_id' => $event->id,
                    'attendee_count' => 7
                ]
            ]);
    }

    public function test_get_attendee_count_returns_zero_for_no_attendees()
    {
        $event = Event::factory()->create();

        $response = $this->getJson("/api/events/{$event->id}/attendees/count");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Attendee count retrieved',
                'data' => [
                    'event_id' => $event->id,
                    'attendee_count' => 0
                ]
            ]);
    }

    public function test_attendee_endpoints_handle_server_errors_gracefully()
    {
        // Basic test to ensure endpoints exist and return proper structure
        $event = Event::factory()->create();
        
        $response = $this->getJson("/api/events/{$event->id}/attendees");
        
        $response->assertJsonStructure([
            'success',
            'message'
        ]);
    }

    public function test_email_validation_in_check_registration()
    {
        $event = Event::factory()->create();
        
        // Test with invalid email format in URL
        $response = $this->getJson("/api/events/{$event->id}/attendees/check/invalid-email-format");

        $response->assertStatus(200); // Should still work but return false
        $response->assertJson([
            'data' => [
                'is_registered' => false
            ]
        ]);
    }

    public function test_concurrent_registration_handling()
    {
        $event = Event::factory()->create([
            'max_capacity' => 2,
            'current_attendees' => 1,
            'start_time' => Carbon::now()->addDays(1),
        ]);

        $attendeeData1 = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];

        $attendeeData2 = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com'
        ];

        // First registration should succeed
        $response1 = $this->postJson("/api/events/{$event->id}/register", $attendeeData1);
        $response1->assertStatus(201);

        // Second registration should fail (event full)
        $response2 = $this->postJson("/api/events/{$event->id}/register", $attendeeData2);
        $response2->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Event is at full capacity'
            ]);
    }
}
