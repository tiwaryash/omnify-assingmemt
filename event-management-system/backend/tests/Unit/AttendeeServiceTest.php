<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AttendeeService;
use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class AttendeeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AttendeeService $attendeeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->attendeeService = new AttendeeService();
    }

    public function test_register_attendee_successful_registration()
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

        $result = $this->attendeeService->registerAttendee($event->id, $attendeeData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Successfully registered for the event', $result['message']);
        $this->assertInstanceOf(Attendee::class, $result['data']);
        $this->assertEquals('John Doe', $result['data']->name);
        $this->assertEquals('john@example.com', $result['data']->email);
        
        // Check that event attendee count was incremented
        $event->refresh();
        $this->assertEquals(51, $event->current_attendees);
        
        // Check database
        $this->assertDatabaseHas('attendees', [
            'event_id' => $event->id,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }

    public function test_register_attendee_fails_for_nonexistent_event()
    {
        $attendeeData = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];

        $result = $this->attendeeService->registerAttendee(999, $attendeeData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Event not found', $result['message']);
        $this->assertNull($result['data']);
    }

    public function test_register_attendee_fails_for_past_event()
    {
        $event = Event::factory()->create([
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addHours(2),
        ]);

        $attendeeData = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];

        $result = $this->attendeeService->registerAttendee($event->id, $attendeeData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Cannot register for past events', $result['message']);
        $this->assertNull($result['data']);
    }

    public function test_register_attendee_fails_for_duplicate_email()
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

        $result = $this->attendeeService->registerAttendee($event->id, $attendeeData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Email is already registered for this event', $result['message']);
        $this->assertNull($result['data']);
    }

    public function test_register_attendee_fails_when_event_full()
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

        $result = $this->attendeeService->registerAttendee($event->id, $attendeeData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Event is at full capacity', $result['message']);
        $this->assertNull($result['data']);
    }

    public function test_get_event_attendees_returns_collection()
    {
        $event = Event::factory()->create();
        
        Attendee::factory()->count(3)->create(['event_id' => $event->id]);

        $attendees = $this->attendeeService->getEventAttendees($event->id);

        $this->assertInstanceOf(Collection::class, $attendees);
        $this->assertCount(3, $attendees);
    }

    public function test_get_paginated_event_attendees_returns_paginator()
    {
        $event = Event::factory()->create();
        
        Attendee::factory()->count(15)->create(['event_id' => $event->id]);

        $paginatedAttendees = $this->attendeeService->getPaginatedEventAttendees($event->id, 10);

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginatedAttendees);
        $this->assertEquals(10, $paginatedAttendees->perPage());
        $this->assertEquals(15, $paginatedAttendees->total());
        $this->assertCount(10, $paginatedAttendees->items());
    }

    public function test_remove_attendee_successful_removal()
    {
        $event = Event::factory()->create([
            'current_attendees' => 5
        ]);
        
        $attendee = Attendee::factory()->create(['event_id' => $event->id]);

        $result = $this->attendeeService->removeAttendee($event->id, $attendee->id);

        $this->assertTrue($result['success']);
        $this->assertEquals('Attendee removed successfully', $result['message']);
        $this->assertNull($result['data']);
        
        // Check that event attendee count was decremented
        $event->refresh();
        $this->assertEquals(4, $event->current_attendees);
        
        // Check that attendee was deleted
        $this->assertDatabaseMissing('attendees', ['id' => $attendee->id]);
    }

    public function test_remove_attendee_fails_for_nonexistent_attendee()
    {
        $event = Event::factory()->create();

        $result = $this->attendeeService->removeAttendee($event->id, 999);

        $this->assertFalse($result['success']);
        $this->assertEquals('Attendee not found for this event', $result['message']);
        $this->assertNull($result['data']);
    }

    public function test_remove_attendee_fails_for_nonexistent_event()
    {
        $result = $this->attendeeService->removeAttendee(999, 1);

        $this->assertFalse($result['success']);
        $this->assertEquals('Attendee not found for this event', $result['message']);
        $this->assertNull($result['data']);
    }

    public function test_remove_attendee_by_email_successful_removal()
    {
        $event = Event::factory()->create([
            'current_attendees' => 3
        ]);
        
        $attendee = Attendee::factory()->create([
            'event_id' => $event->id,
            'email' => 'john@example.com'
        ]);

        $result = $this->attendeeService->removeAttendeeByEmail($event->id, 'john@example.com');

        $this->assertTrue($result['success']);
        $this->assertEquals('Attendee removed successfully', $result['message']);
        
        // Check that event attendee count was decremented
        $event->refresh();
        $this->assertEquals(2, $event->current_attendees);
        
        // Check that attendee was deleted
        $this->assertDatabaseMissing('attendees', ['id' => $attendee->id]);
    }

    public function test_remove_attendee_by_email_fails_for_nonexistent_email()
    {
        $event = Event::factory()->create();

        $result = $this->attendeeService->removeAttendeeByEmail($event->id, 'nonexistent@example.com');

        $this->assertFalse($result['success']);
        $this->assertEquals('Attendee not found for this event', $result['message']);
    }

    public function test_get_attendee_by_id_returns_attendee()
    {
        $attendee = Attendee::factory()->create(['name' => 'John Doe']);

        $foundAttendee = $this->attendeeService->getAttendeeById($attendee->id);

        $this->assertInstanceOf(Attendee::class, $foundAttendee);
        $this->assertEquals($attendee->id, $foundAttendee->id);
        $this->assertEquals('John Doe', $foundAttendee->name);
        $this->assertNotNull($foundAttendee->event); // Should be loaded with event relationship
    }

    public function test_get_attendee_by_id_returns_null_for_nonexistent()
    {
        $foundAttendee = $this->attendeeService->getAttendeeById(999);

        $this->assertNull($foundAttendee);
    }

    public function test_is_email_registered_returns_true_for_registered_email()
    {
        $event = Event::factory()->create();
        Attendee::factory()->create([
            'event_id' => $event->id,
            'email' => 'registered@example.com'
        ]);

        $result = $this->attendeeService->isEmailRegistered($event->id, 'registered@example.com');

        $this->assertTrue($result);
    }

    public function test_is_email_registered_returns_false_for_unregistered_email()
    {
        $event = Event::factory()->create();

        $result = $this->attendeeService->isEmailRegistered($event->id, 'unregistered@example.com');

        $this->assertFalse($result);
    }

    public function test_get_attendee_count_returns_correct_count()
    {
        $event = Event::factory()->create();
        Attendee::factory()->count(7)->create(['event_id' => $event->id]);

        $count = $this->attendeeService->getAttendeeCount($event->id);

        $this->assertEquals(7, $count);
    }

    public function test_search_attendees_by_name()
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

        $results = $this->attendeeService->searchAttendees($event->id, 'John');

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('name', 'John Doe'));
        $this->assertTrue($results->contains('name', 'John Wilson'));
    }

    public function test_search_attendees_by_email()
    {
        $event = Event::factory()->create();
        
        Attendee::factory()->create([
            'event_id' => $event->id,
            'name' => 'John Doe',
            'email' => 'john@gmail.com'
        ]);
        
        Attendee::factory()->create([
            'event_id' => $event->id,
            'name' => 'Jane Smith',
            'email' => 'jane@yahoo.com'
        ]);
        
        Attendee::factory()->create([
            'event_id' => $event->id,
            'name' => 'Bob Wilson',
            'email' => 'bob@gmail.com'
        ]);

        $results = $this->attendeeService->searchAttendees($event->id, 'gmail');

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('email', 'john@gmail.com'));
        $this->assertTrue($results->contains('email', 'bob@gmail.com'));
    }

    public function test_search_attendees_returns_empty_for_no_matches()
    {
        $event = Event::factory()->create();
        Attendee::factory()->count(3)->create(['event_id' => $event->id]);

        $results = $this->attendeeService->searchAttendees($event->id, 'nonexistent');

        $this->assertCount(0, $results);
    }
}
