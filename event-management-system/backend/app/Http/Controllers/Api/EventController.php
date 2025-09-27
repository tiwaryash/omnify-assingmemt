<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EventController extends Controller
{
    protected EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * Display a listing of upcoming events.
     * 
     * @OA\Get(
     *     path="/api/events",
     *     summary="Get paginated list of upcoming events",
     *     description="Retrieve a paginated list of all upcoming events",
     *     operationId="getEvents",
     *     tags={"Events"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of events per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10, minimum=1, maximum=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Events retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Events retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Event")
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="to", type="integer", example=10)
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
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $events = $this->eventService->getPaginatedUpcomingEvents($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Events retrieved successfully',
                'data' => $events->items(),
                'pagination' => [
                    'current_page' => $events->currentPage(),
                    'last_page' => $events->lastPage(),
                    'per_page' => $events->perPage(),
                    'total' => $events->total(),
                    'from' => $events->firstItem(),
                    'to' => $events->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created event.
     * 
     * @OA\Post(
     *     path="/api/events",
     *     summary="Create a new event",
     *     description="Create a new event with the provided details",
     *     operationId="createEvent",
     *     tags={"Events"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Event data",
     *         @OA\JsonContent(
     *             required={"name", "location", "start_time", "end_time", "max_capacity"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Tech Conference 2025"),
     *             @OA\Property(property="location", type="string", maxLength=255, example="Mumbai, India"),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2025-12-01T10:00:00Z"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2025-12-01T18:00:00Z"),
     *             @OA\Property(property="max_capacity", type="integer", minimum=1, example=100),
     *             @OA\Property(property="timezone", type="string", example="Asia/Kolkata")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Event created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Event")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'max_capacity' => 'required|integer|min:1',
            'timezone' => 'nullable|string|in:' . implode(',', timezone_identifiers_list()),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $event = $this->eventService->createEvent($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully',
                'data' => $event
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified event.
     * 
     * @OA\Get(
     *     path="/api/events/{id}",
     *     summary="Get event by ID",
     *     description="Retrieve a specific event with its statistics",
     *     operationId="getEvent",
     *     tags={"Events"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="event", ref="#/components/schemas/Event"),
     *                 @OA\Property(property="statistics", ref="#/components/schemas/EventStatistics")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFound")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $event = $this->eventService->getEventById((int)$id);

            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found'
                ], 404);
            }

            $statistics = $this->eventService->getEventStatistics((int)$id);

            return response()->json([
                'success' => true,
                'message' => 'Event retrieved successfully',
                'data' => [
                    'event' => $event,
                    'statistics' => $statistics
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified event.
     * 
     * @OA\Put(
     *     path="/api/events/{id}",
     *     summary="Update an event",
     *     description="Update an existing event with new data",
     *     operationId="updateEvent",
     *     tags={"Events"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Updated event data",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=255, example="Updated Tech Conference 2025"),
     *             @OA\Property(property="location", type="string", maxLength=255, example="Delhi, India"),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2025-12-01T10:00:00Z"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2025-12-01T18:00:00Z"),
     *             @OA\Property(property="max_capacity", type="integer", minimum=1, example=150),
     *             @OA\Property(property="timezone", type="string", example="Asia/Kolkata")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Event")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFound")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'location' => 'sometimes|required|string|max:255',
            'start_time' => 'sometimes|required|date|after:now',
            'end_time' => 'sometimes|required|date|after:start_time',
            'max_capacity' => 'sometimes|required|integer|min:1',
            'timezone' => 'nullable|string|in:' . implode(',', timezone_identifiers_list()),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $event = $this->eventService->updateEvent((int)$id, $request->all());

            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully',
                'data' => $event
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified event.
     * 
     * @OA\Delete(
     *     path="/api/events/{id}",
     *     summary="Delete an event",
     *     description="Delete an existing event",
     *     operationId="deleteEvent",
     *     tags={"Events"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Event ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFound")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->eventService->deleteEvent((int)$id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting event',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
