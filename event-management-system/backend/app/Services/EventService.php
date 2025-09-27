<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EventService
{
    /**
     * Get all upcoming events
     */
    public function getUpcomingEvents(): Collection
    {
        return Event::upcoming()
            ->orderBy('start_time', 'asc')
            ->get();
    }

    /**
     * Get paginated upcoming events
     */
    public function getPaginatedUpcomingEvents(int $perPage = 10): LengthAwarePaginator
    {
        return Event::upcoming()
            ->orderBy('start_time', 'asc')
            ->paginate($perPage);
    }

    /**
     * Create a new event
     */
    public function createEvent(array $data): Event
    {
        // Convert timezone if provided
        if (isset($data['start_time'])) {
            $data['start_time'] = Carbon::parse($data['start_time'], $data['timezone'] ?? 'Asia/Kolkata')
                ->utc();
        }
        
        if (isset($data['end_time'])) {
            $data['end_time'] = Carbon::parse($data['end_time'], $data['timezone'] ?? 'Asia/Kolkata')
                ->utc();
        }

        return Event::create($data);
    }

    /**
     * Get event by ID
     */
    public function getEventById(int $id): ?Event
    {
        return Event::find($id);
    }

    /**
     * Update event
     */
    public function updateEvent(int $id, array $data): ?Event
    {
        $event = Event::find($id);
        
        if (!$event) {
            return null;
        }

        // Convert timezone if provided
        if (isset($data['start_time'])) {
            $data['start_time'] = Carbon::parse($data['start_time'], $data['timezone'] ?? $event->timezone)
                ->utc();
        }
        
        if (isset($data['end_time'])) {
            $data['end_time'] = Carbon::parse($data['end_time'], $data['timezone'] ?? $event->timezone)
                ->utc();
        }

        $event->update($data);
        return $event;
    }

    /**
     * Delete event
     */
    public function deleteEvent(int $id): bool
    {
        $event = Event::find($id);
        
        if (!$event) {
            return false;
        }

        return $event->delete();
    }

    /**
     * Check if event has available capacity
     */
    public function hasAvailableCapacity(int $eventId): bool
    {
        $event = Event::find($eventId);
        return $event && $event->hasAvailableCapacity();
    }

    /**
     * Get events by location
     */
    public function getEventsByLocation(string $location): Collection
    {
        return Event::where('location', 'like', '%' . $location . '%')
            ->upcoming()
            ->orderBy('start_time', 'asc')
            ->get();
    }

    /**
     * Get events in date range
     */
    public function getEventsInDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return Event::whereBetween('start_time', [$startDate, $endDate])
            ->orderBy('start_time', 'asc')
            ->get();
    }

    /**
     * Get event statistics
     */
    public function getEventStatistics(int $eventId): array
    {
        $event = Event::find($eventId);
        
        if (!$event) {
            return [];
        }

        return [
            'total_capacity' => $event->max_capacity,
            'current_attendees' => $event->current_attendees,
            'remaining_capacity' => $event->getRemainingCapacity(),
            'capacity_percentage' => round(($event->current_attendees / $event->max_capacity) * 100, 2),
            'is_full' => !$event->hasAvailableCapacity(),
            'is_upcoming' => $event->isUpcoming(),
        ];
    }
}
