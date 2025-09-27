<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\AttendeeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/**
 * @OA\Get(
 *     path="/api/health",
 *     summary="Health check endpoint",
 *     description="Check if the API is running and healthy",
 *     operationId="healthCheck",
 *     tags={"System"},
 *     @OA\Response(
 *         response=200,
 *         description="API is healthy",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="API is running"),
 *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-09-27T10:00:00.000000Z")
 *         )
 *     )
 * )
 */
// Health check route
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now()->toISOString()
    ]);
});

// Event routes
Route::apiResource('events', EventController::class);

// Attendee routes for specific events
Route::prefix('events/{event}')->group(function () {
    // Register attendee for event
    Route::post('/register', [AttendeeController::class, 'register']);
    
    // Get attendees for event
    Route::get('/attendees', [AttendeeController::class, 'getEventAttendees']);
    
    // Remove attendee from event
    Route::delete('/attendees/{attendee}', [AttendeeController::class, 'removeAttendee']);
    
    // Check if email is registered for event
    Route::get('/attendees/check/{email}', [AttendeeController::class, 'checkRegistration']);
    
    // Get attendee count for event
    Route::get('/attendees/count', [AttendeeController::class, 'getAttendeeCount']);
});

// CORS preflight
Route::options('{any}', function () {
    return response()->json([], 200);
})->where('any', '.*');
