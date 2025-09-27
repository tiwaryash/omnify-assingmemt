<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class AttendeeService
{
    /**
     * Register an attendee for an event
     */
    public function registerAttendee(int $eventId, array $attendeeData): array
    {
        return DB::transaction(function () use ($eventId, $attendeeData) {
            $event = Event::lockForUpdate()->find($eventId);
            
            if (!$event) {
                return [
                    'success' => false,
                    'message' => 'Event not found',
                    'data' => null
                ];
            }

            // Check if event is upcoming
            if (!$event->isUpcoming()) {
                return [
                    'success' => false,
                    'message' => 'Cannot register for past events',
                    'data' => null
                ];
            }

            // Check if attendee is already registered
            if (Attendee::isRegisteredForEvent($eventId, $attendeeData['email'])) {
                return [
                    'success' => false,
                    'message' => 'Email is already registered for this event',
                    'data' => null
                ];
            }

            // Check capacity
            if (!$event->hasAvailableCapacity()) {
                return [
                    'success' => false,
                    'message' => 'Event is at full capacity',
                    'data' => null
                ];
            }

            // Create attendee
            $attendee = Attendee::create([
                'event_id' => $eventId,
                'name' => $attendeeData['name'],
                'email' => $attendeeData['email']
            ]);

            // Update event capacity
            $event->increment('current_attendees');

            return [
                'success' => true,
                'message' => 'Successfully registered for the event',
                'data' => $attendee->load('event')
            ];
        });
    }

    /**
     * Get all attendees for an event
     */
    public function getEventAttendees(int $eventId): Collection
    {
        return Attendee::where('event_id', $eventId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get paginated attendees for an event
     */
    public function getPaginatedEventAttendees(int $eventId, int $perPage = 10): LengthAwarePaginator
    {
        return Attendee::where('event_id', $eventId)
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }

    /**
     * Remove attendee from event
     */
    public function removeAttendee(int $eventId, int $attendeeId): array
    {
        return DB::transaction(function () use ($eventId, $attendeeId) {
            $attendee = Attendee::where('event_id', $eventId)
                ->where('id', $attendeeId)
                ->first();

            if (!$attendee) {
                return [
                    'success' => false,
                    'message' => 'Attendee not found for this event',
                    'data' => null
                ];
            }

            $event = Event::lockForUpdate()->find($eventId);
            
            if (!$event) {
                return [
                    'success' => false,
                    'message' => 'Event not found',
                    'data' => null
                ];
            }

            // Delete attendee
            $attendee->delete();

            // Update event capacity
            $event->decrement('current_attendees');

            return [
                'success' => true,
                'message' => 'Attendee removed successfully',
                'data' => null
            ];
        });
    }

    /**
     * Remove attendee by email
     */
    public function removeAttendeeByEmail(int $eventId, string $email): array
    {
        return DB::transaction(function () use ($eventId, $email) {
            $attendee = Attendee::where('event_id', $eventId)
                ->where('email', $email)
                ->first();

            if (!$attendee) {
                return [
                    'success' => false,
                    'message' => 'Attendee not found for this event',
                    'data' => null
                ];
            }

            $event = Event::lockForUpdate()->find($eventId);
            
            if (!$event) {
                return [
                    'success' => false,
                    'message' => 'Event not found',
                    'data' => null
                ];
            }

            // Delete attendee
            $attendee->delete();

            // Update event capacity
            $event->decrement('current_attendees');

            return [
                'success' => true,
                'message' => 'Attendee removed successfully',
                'data' => null
            ];
        });
    }

    /**
     * Get attendee by ID
     */
    public function getAttendeeById(int $attendeeId): ?Attendee
    {
        return Attendee::with('event')->find($attendeeId);
    }

    /**
     * Check if email is registered for event
     */
    public function isEmailRegistered(int $eventId, string $email): bool
    {
        return Attendee::isRegisteredForEvent($eventId, $email);
    }

    /**
     * Get attendee count for event
     */
    public function getAttendeeCount(int $eventId): int
    {
        return Attendee::where('event_id', $eventId)->count();
    }

    /**
     * Search attendees by name or email
     */
    public function searchAttendees(int $eventId, string $query): Collection
    {
        return Attendee::where('event_id', $eventId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('email', 'like', '%' . $query . '%');
            })
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
