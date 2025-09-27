<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\EventService;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class EventServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EventService $eventService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventService = new EventService();
    }

    public function test_get_upcoming_events_returns_collection()
    {
        // Create upcoming events
        Event::factory()->create([
            'name' => 'Upcoming Event 1',
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addHours(2),
        ]);
        
        Event::factory()->create([
            'name' => 'Upcoming Event 2', 
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(2)->addHours(2),
        ]);

        // Create past event (should not be included)
        Event::factory()->create([
            'name' => 'Past Event',
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addHours(2),
        ]);

        $events = $this->eventService->getUpcomingEvents();

        $this->assertInstanceOf(Collection::class, $events);
        $this->assertCount(2, $events);
        $this->assertEquals('Upcoming Event 1', $events->first()->name);
    }

    public function test_get_paginated_upcoming_events_returns_paginator()
    {
        // Create multiple upcoming events
        Event::factory()->count(15)->create([
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addHours(2),
        ]);

        $paginatedEvents = $this->eventService->getPaginatedUpcomingEvents(10);

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginatedEvents);
        $this->assertEquals(10, $paginatedEvents->perPage());
        $this->assertEquals(15, $paginatedEvents->total());
        $this->assertCount(10, $paginatedEvents->items());
    }

    public function test_create_event_with_valid_data()
    {
        $eventData = [
            'name' => 'Test Event',
            'location' => 'Test Location',
            'start_time' => '2025-12-01 10:00:00',
            'end_time' => '2025-12-01 12:00:00',
            'max_capacity' => 100,
            'timezone' => 'Asia/Kolkata'
        ];

        $event = $this->eventService->createEvent($eventData);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertEquals('Test Event', $event->name);
        $this->assertEquals('Test Location', $event->location);
        $this->assertEquals(100, $event->max_capacity);
        $this->assertEquals(0, $event->current_attendees);
        $this->assertDatabaseHas('events', ['name' => 'Test Event']);
    }

    public function test_create_event_converts_timezone_to_utc()
    {
        $eventData = [
            'name' => 'Timezone Test Event',
            'location' => 'Test Location',
            'start_time' => '2025-12-01 15:30:00', // 3:30 PM IST
            'end_time' => '2025-12-01 17:30:00',   // 5:30 PM IST
            'max_capacity' => 50,
            'timezone' => 'Asia/Kolkata'
        ];

        $event = $this->eventService->createEvent($eventData);

        // IST is UTC+5:30, so 15:30 IST should be 10:00 UTC
        $this->assertEquals('10:00:00', $event->start_time->format('H:i:s'));
        $this->assertEquals('12:00:00', $event->end_time->format('H:i:s'));
    }

    public function test_get_event_by_id_returns_event()
    {
        $event = Event::factory()->create(['name' => 'Test Event']);

        $foundEvent = $this->eventService->getEventById($event->id);

        $this->assertInstanceOf(Event::class, $foundEvent);
        $this->assertEquals($event->id, $foundEvent->id);
        $this->assertEquals('Test Event', $foundEvent->name);
    }

    public function test_get_event_by_id_returns_null_for_nonexistent()
    {
        $foundEvent = $this->eventService->getEventById(999);

        $this->assertNull($foundEvent);
    }

    public function test_update_event_with_valid_data()
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

        $updatedEvent = $this->eventService->updateEvent($event->id, $updateData);

        $this->assertInstanceOf(Event::class, $updatedEvent);
        $this->assertEquals('Updated Name', $updatedEvent->name);
        $this->assertEquals('Updated Location', $updatedEvent->location);
        $this->assertEquals(100, $updatedEvent->max_capacity);
    }

    public function test_update_event_returns_null_for_nonexistent()
    {
        $updateData = ['name' => 'Updated Name'];
        
        $result = $this->eventService->updateEvent(999, $updateData);

        $this->assertNull($result);
    }

    public function test_delete_event_removes_event()
    {
        $event = Event::factory()->create(['name' => 'To Delete']);

        $result = $this->eventService->deleteEvent($event->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_delete_event_returns_false_for_nonexistent()
    {
        $result = $this->eventService->deleteEvent(999);

        $this->assertFalse($result);
    }

    public function test_has_available_capacity_returns_true_when_space_available()
    {
        $event = Event::factory()->create([
            'max_capacity' => 100,
            'current_attendees' => 50
        ]);

        $result = $this->eventService->hasAvailableCapacity($event->id);

        $this->assertTrue($result);
    }

    public function test_has_available_capacity_returns_false_when_full()
    {
        $event = Event::factory()->create([
            'max_capacity' => 100,
            'current_attendees' => 100
        ]);

        $result = $this->eventService->hasAvailableCapacity($event->id);

        $this->assertFalse($result);
    }

    public function test_get_events_by_location_filters_correctly()
    {
        Event::factory()->create([
            'name' => 'Delhi Event',
            'location' => 'New Delhi, India',
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addHours(2),
        ]);

        Event::factory()->create([
            'name' => 'Mumbai Event',
            'location' => 'Mumbai, India',
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addHours(2),
        ]);

        Event::factory()->create([
            'name' => 'Another Delhi Event',
            'location' => 'Delhi NCR',
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addHours(2),
        ]);

        $delhiEvents = $this->eventService->getEventsByLocation('Delhi');

        $this->assertCount(2, $delhiEvents);
        $this->assertTrue($delhiEvents->contains('name', 'Delhi Event'));
        $this->assertTrue($delhiEvents->contains('name', 'Another Delhi Event'));
    }

    public function test_get_events_in_date_range_filters_correctly()
    {
        $startDate = Carbon::now()->addDays(5);
        $endDate = Carbon::now()->addDays(10);

        // Event within range
        Event::factory()->create([
            'name' => 'Within Range',
            'start_time' => Carbon::now()->addDays(7),
            'end_time' => Carbon::now()->addDays(7)->addHours(2),
        ]);

        // Event before range
        Event::factory()->create([
            'name' => 'Before Range',
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(2)->addHours(2),
        ]);

        // Event after range
        Event::factory()->create([
            'name' => 'After Range',
            'start_time' => Carbon::now()->addDays(15),
            'end_time' => Carbon::now()->addDays(15)->addHours(2),
        ]);

        $eventsInRange = $this->eventService->getEventsInDateRange($startDate, $endDate);

        $this->assertCount(1, $eventsInRange);
        $this->assertEquals('Within Range', $eventsInRange->first()->name);
    }

    public function test_get_event_statistics_returns_correct_data()
    {
        $event = Event::factory()->create([
            'max_capacity' => 100,
            'current_attendees' => 75,
            'start_time' => Carbon::now()->addDays(1),
        ]);

        $statistics = $this->eventService->getEventStatistics($event->id);

        $this->assertEquals(100, $statistics['total_capacity']);
        $this->assertEquals(75, $statistics['current_attendees']);
        $this->assertEquals(25, $statistics['remaining_capacity']);
        $this->assertEquals(75.0, $statistics['capacity_percentage']);
        $this->assertFalse($statistics['is_full']);
        $this->assertTrue($statistics['is_upcoming']);
    }

    public function test_get_event_statistics_returns_empty_for_nonexistent()
    {
        $statistics = $this->eventService->getEventStatistics(999);

        $this->assertEquals([], $statistics);
    }
}
