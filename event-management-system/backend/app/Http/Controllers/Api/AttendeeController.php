<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendeeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AttendeeController extends Controller
{
    protected AttendeeService $attendeeService;

    public function __construct(AttendeeService $attendeeService)
    {
        $this->attendeeService = $attendeeService;
    }

    /**
     * Register an attendee for a specific event
     * 
     * @OA\Post(
     *     path="/api/events/{event_id}/register",
     *     summary="Register attendee for event",
     *     description="Register a new attendee for a specific event",
     *     operationId="registerAttendee",
     *     tags={"Attendees"},
     *     @OA\Parameter(
     *         name="event_id",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Attendee registration data",
     *         @OA\JsonContent(
     *             required={"name", "email"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="john.doe@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Attendee registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully registered for the event"),
     *             @OA\Property(property="data", ref="#/components/schemas/AttendeeWithEvent")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (past event, full capacity)",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFound")
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Email already registered",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function register(Request $request, string $eventId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->attendeeService->registerAttendee((int)$eventId, $request->all());

            if (!$result['success']) {
                $statusCode = match ($result['message']) {
                    'Event not found' => 404,
                    'Cannot register for past events' => 400,
                    'Email is already registered for this event' => 409,
                    'Event is at full capacity' => 400,
                    default => 400
                };

                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], $statusCode);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data']
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error registering attendee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all attendees for a specific event
     * 
     * @OA\Get(
     *     path="/api/events/{event_id}/attendees",
     *     summary="Get event attendees",
     *     description="Get paginated list of attendees for a specific event with optional search",
     *     operationId="getEventAttendees",
     *     tags={"Attendees"},
     *     @OA\Parameter(
     *         name="event_id",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of attendees per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10, minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name or email",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attendees retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Attendees retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Attendee")
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="to", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function getEventAttendees(Request $request, string $eventId): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search');

            if ($search) {
                $attendees = $this->attendeeService->searchAttendees((int)$eventId, $search);
                return response()->json([
                    'success' => true,
                    'message' => 'Attendees retrieved successfully',
                    'data' => $attendees
                ], 200);
            }

            $attendees = $this->attendeeService->getPaginatedEventAttendees((int)$eventId, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Attendees retrieved successfully',
                'data' => $attendees->items(),
                'pagination' => [
                    'current_page' => $attendees->currentPage(),
                    'last_page' => $attendees->lastPage(),
                    'per_page' => $attendees->perPage(),
                    'total' => $attendees->total(),
                    'from' => $attendees->firstItem(),
                    'to' => $attendees->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving attendees',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove an attendee from an event
     * 
     * @OA\Delete(
     *     path="/api/events/{event_id}/attendees/{attendee_id}",
     *     summary="Remove attendee from event",
     *     description="Remove a specific attendee from an event",
     *     operationId="removeAttendee",
     *     tags={"Attendees"},
     *     @OA\Parameter(
     *         name="event_id",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="attendee_id",
     *         in="path",
     *         description="Attendee ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attendee removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Attendee removed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Attendee or event not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFound")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function removeAttendee(string $eventId, string $attendeeId): JsonResponse
    {
        try {
            $result = $this->attendeeService->removeAttendee((int)$eventId, (int)$attendeeId);

            if (!$result['success']) {
                $statusCode = match ($result['message']) {
                    'Attendee not found for this event' => 404,
                    'Event not found' => 404,
                    default => 400
                };

                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], $statusCode);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message']
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing attendee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if an email is registered for an event
     * 
     * @OA\Get(
     *     path="/api/events/{event_id}/attendees/check/{email}",
     *     summary="Check email registration status",
     *     description="Check if a specific email is registered for an event",
     *     operationId="checkRegistration",
     *     tags={"Attendees"},
     *     @OA\Parameter(
     *         name="event_id",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         description="Email address to check",
     *         required=true,
     *         @OA\Schema(type="string", format="email")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registration status checked",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Registration status checked"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="is_registered", type="boolean", example=true),
     *                 @OA\Property(property="event_id", type="integer", example=1),
     *                 @OA\Property(property="email", type="string", format="email", example="john.doe@example.com")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function checkRegistration(string $eventId, string $email): JsonResponse
    {
        try {
            $isRegistered = $this->attendeeService->isEmailRegistered((int)$eventId, $email);

            return response()->json([
                'success' => true,
                'message' => 'Registration status checked',
                'data' => [
                    'is_registered' => $isRegistered,
                    'event_id' => (int)$eventId,
                    'email' => $email
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking registration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendee count for an event
     * 
     * @OA\Get(
     *     path="/api/events/{event_id}/attendees/count",
     *     summary="Get attendee count",
     *     description="Get the total number of attendees for a specific event",
     *     operationId="getAttendeeCount",
     *     tags={"Attendees"},
     *     @OA\Parameter(
     *         name="event_id",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attendee count retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Attendee count retrieved"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="event_id", type="integer", example=1),
     *                 @OA\Property(property="attendee_count", type="integer", example=25)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function getAttendeeCount(string $eventId): JsonResponse
    {
        try {
            $count = $this->attendeeService->getAttendeeCount((int)$eventId);

            return response()->json([
                'success' => true,
                'message' => 'Attendee count retrieved',
                'data' => [
                    'event_id' => (int)$eventId,
                    'attendee_count' => $count
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving attendee count',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}