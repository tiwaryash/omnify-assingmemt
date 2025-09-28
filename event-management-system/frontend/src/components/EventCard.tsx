'use client';

import React from 'react';
import { Calendar, MapPin, Users, Clock } from 'lucide-react';
import { Event } from '@/lib/api';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { formatEventTimeRange, calculateEventDuration, getTimezoneAbbreviation } from '@/lib/timezone';

interface EventCardProps {
  event: Event;
  onRegister?: (event: Event) => void;
  onViewDetails?: (event: Event) => void;
  className?: string;
  displayTimezone?: string;
}

export function EventCard({ 
  event, 
  onRegister, 
  onViewDetails, 
  className,
  displayTimezone = 'Asia/Kolkata'
}: EventCardProps) {
  const isFullyBooked = event.current_attendees >= event.max_capacity;
  const availableSpots = event.max_capacity - event.current_attendees;

  // Use timezone-aware formatting
  const eventTimeRange = formatEventTimeRange(event.start_time, event.end_time, displayTimezone);
  const eventDuration = calculateEventDuration(event.start_time, event.end_time);
  const timezoneAbbr = getTimezoneAbbreviation(displayTimezone);

  return (
    <div className={cn(
      "bg-white rounded-lg shadow-md border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-200",
      className
    )}>
      {/* Event Header */}
      <div className="mb-4">
        <h3 className="text-xl font-semibold text-gray-900 mb-2">
          {event.name}
        </h3>
        
        {/* Location */}
        <div className="flex items-center text-gray-600 mb-2">
          <MapPin className="h-4 w-4 mr-2 flex-shrink-0" />
          <span className="text-sm">{event.location}</span>
        </div>

        {/* Date and Time */}
        <div className="flex items-center text-gray-600 mb-2">
          <Calendar className="h-4 w-4 mr-2 flex-shrink-0" />
          <span className="text-sm">{eventTimeRange}</span>
        </div>

        {/* Duration */}
        <div className="flex items-center text-gray-600 mb-2">
          <Clock className="h-4 w-4 mr-2 flex-shrink-0" />
          <span className="text-sm">Duration: {eventDuration}</span>
        </div>
      </div>

      {/* Capacity Info */}
      <div className="mb-4">
        <div className="flex items-center justify-between mb-2">
          <div className="flex items-center text-gray-600">
            <Users className="h-4 w-4 mr-2" />
            <span className="text-sm">
              {event.current_attendees} / {event.max_capacity} registered
            </span>
          </div>
          <span className={cn(
            "text-xs px-2 py-1 rounded-full font-medium",
            isFullyBooked 
              ? "bg-red-100 text-red-700" 
              : availableSpots <= 5 
                ? "bg-yellow-100 text-yellow-700"
                : "bg-green-100 text-green-700"
          )}>
            {isFullyBooked 
              ? "Fully Booked" 
              : `${availableSpots} spots left`
            }
          </span>
        </div>

        {/* Progress Bar */}
        <div className="w-full bg-gray-200 rounded-full h-2">
          <div 
            className={cn(
              "h-2 rounded-full transition-all duration-300",
              isFullyBooked 
                ? "bg-red-500" 
                : availableSpots <= 5 
                  ? "bg-yellow-500"
                  : "bg-green-500"
            )}
            style={{ 
              width: `${Math.min((event.current_attendees / event.max_capacity) * 100, 100)}%` 
            }}
          />
        </div>
      </div>

      {/* Action Buttons */}
      <div className="flex gap-2 pt-4 border-t border-gray-100">
        {onViewDetails && (
          <Button
            variant="outline"
            size="sm"
            onClick={() => onViewDetails(event)}
            className="flex-1 text-gray-700 border-gray-300 hover:bg-gray-50"
          >
            View Attendees
          </Button>
        )}
        
        {onRegister && (
          <Button
            size="sm"
            onClick={() => onRegister(event)}
            disabled={isFullyBooked}
            variant={isFullyBooked ? "secondary" : "default"}
            className="flex-1 font-medium"
          >
            {isFullyBooked ? 'Fully Booked' : 'Register'}
          </Button>
        )}
      </div>

      {/* Timezone Info */}
      <div className="mt-2 text-xs text-gray-500 text-center">
        Times shown in {timezoneAbbr}
      </div>
    </div>
  );
}
