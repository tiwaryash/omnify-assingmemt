<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'location',
        'start_time',
        'end_time',
        'max_capacity',
        'current_attendees',
        'timezone'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'max_capacity' => 'integer',
        'current_attendees' => 'integer',
    ];

    /**
     * Get all attendees for this event
     */
    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    /**
     * Check if event has available capacity
     */
    public function hasAvailableCapacity(): bool
    {
        return $this->current_attendees < $this->max_capacity;
    }

    /**
     * Get remaining capacity
     */
    public function getRemainingCapacity(): int
    {
        return $this->max_capacity - $this->current_attendees;
    }

    /**
     * Check if event is upcoming (start time is in the future)
     */
    public function isUpcoming(): bool
    {
        return $this->start_time > now();
    }

    /**
     * Scope to get upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    /**
     * Get start time in specific timezone
     */
    public function getStartTimeInTimezone($timezone = null)
    {
        $tz = $timezone ?? $this->timezone ?? 'Asia/Kolkata';
        return $this->start_time->setTimezone($tz);
    }

    /**
     * Get end time in specific timezone
     */
    public function getEndTimeInTimezone($timezone = null)
    {
        $tz = $timezone ?? $this->timezone ?? 'Asia/Kolkata';
        return $this->end_time->setTimezone($tz);
    }
}
