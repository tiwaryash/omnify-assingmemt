<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendee extends Model
{
    use HasFactory;
    protected $fillable = [
        'event_id',
        'name',
        'email'
    ];

    protected $casts = [
        'event_id' => 'integer',
    ];

    /**
     * Get the event that this attendee belongs to
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Check if this attendee is registered for a specific event
     */
    public static function isRegisteredForEvent(int $eventId, string $email): bool
    {
        return self::where('event_id', $eventId)
            ->where('email', $email)
            ->exists();
    }
}
