<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\Schema(
 *     schema="Event",
 *     type="object",
 *     title="Event",
 *     description="Event model",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", maxLength=255, example="Tech Conference 2025"),
 *     @OA\Property(property="location", type="string", maxLength=255, example="Mumbai, India"),
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2025-12-01T10:00:00.000000Z"),
 *     @OA\Property(property="end_time", type="string", format="date-time", example="2025-12-01T18:00:00.000000Z"),
 *     @OA\Property(property="max_capacity", type="integer", example=100),
 *     @OA\Property(property="current_attendees", type="integer", example=25),
 *     @OA\Property(property="timezone", type="string", example="Asia/Kolkata"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-27T10:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-27T10:00:00.000000Z")
 * )
 * 
 * @OA\Schema(
 *     schema="Attendee",
 *     type="object",
 *     title="Attendee",
 *     description="Attendee model",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="event_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", maxLength=255, example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", maxLength=255, example="john.doe@example.com"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-27T10:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-27T10:00:00.000000Z")
 * )
 * 
 * @OA\Schema(
 *     schema="AttendeeWithEvent",
 *     type="object",
 *     title="Attendee with Event",
 *     description="Attendee model with event relationship",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Attendee"),
 *         @OA\Schema(
 *             @OA\Property(property="event", ref="#/components/schemas/Event")
 *         )
 *     }
 * )
 * 
 * @OA\Schema(
 *     schema="EventStatistics",
 *     type="object",
 *     title="Event Statistics",
 *     description="Event capacity and status statistics",
 *     @OA\Property(property="total_capacity", type="integer", example=100),
 *     @OA\Property(property="current_attendees", type="integer", example=25),
 *     @OA\Property(property="remaining_capacity", type="integer", example=75),
 *     @OA\Property(property="capacity_percentage", type="number", format="float", example=25.0),
 *     @OA\Property(property="is_full", type="boolean", example=false),
 *     @OA\Property(property="is_upcoming", type="boolean", example=true)
 * )
 * 
 * @OA\Schema(
 *     schema="Error",
 *     type="object",
 *     title="Error Response",
 *     description="Standard error response",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="An error occurred"),
 *     @OA\Property(property="error", type="string", example="Detailed error message")
 * )
 * 
 * @OA\Schema(
 *     schema="NotFound",
 *     type="object",
 *     title="Not Found Response",
 *     description="Resource not found response",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Resource not found")
 * )
 * 
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     title="Validation Error Response",
 *     description="Validation error response",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Validation failed"),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         additionalProperties={
 *             "type": "array",
 *             "items": {
 *                 "type": "string"
 *             }
 *         },
 *         example={
 *             "name": {"The name field is required."},
 *             "email": {"The email must be a valid email address."}
 *         }
 *     )
 * )
 */
class SchemaDefinitions
{
    // This class is only used for Swagger schema definitions
}
