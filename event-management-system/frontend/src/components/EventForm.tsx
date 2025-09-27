'use client';

import React, { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { format } from 'date-fns';
import { CalendarIcon, Loader2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { CreateEventData, eventApi } from '@/lib/api';
import { cn } from '@/lib/utils';

// Validation schema
const eventSchema = z.object({
  name: z.string().min(1, 'Event name is required').max(255, 'Event name is too long'),
  location: z.string().min(1, 'Location is required').max(255, 'Location is too long'),
  start_time: z.string().min(1, 'Start time is required'),
  end_time: z.string().min(1, 'End time is required'),
  max_capacity: z.number().min(1, 'Capacity must be at least 1').max(10000, 'Capacity is too high'),
  timezone: z.string().optional(),
}).refine((data) => {
  const startDate = new Date(data.start_time);
  const endDate = new Date(data.end_time);
  return endDate > startDate;
}, {
  message: "End time must be after start time",
  path: ["end_time"],
}).refine((data) => {
  const startDate = new Date(data.start_time);
  const now = new Date();
  return startDate > now;
}, {
  message: "Event must be scheduled for the future",
  path: ["start_time"],
});

type EventFormData = z.infer<typeof eventSchema>;

interface EventFormProps {
  onSuccess?: (event: any) => void;
  onCancel?: () => void;
  className?: string;
}

export function EventForm({ onSuccess, onCancel, className }: EventFormProps) {
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    formState: { errors },
    reset,
    watch,
  } = useForm<EventFormData>({
    resolver: zodResolver(eventSchema),
    defaultValues: {
      timezone: 'Asia/Kolkata',
    },
  });

  const startTime = watch('start_time');

  const onSubmit = async (data: EventFormData) => {
    setIsSubmitting(true);
    setSubmitError(null);

    try {
      const eventData: CreateEventData = {
        name: data.name,
        location: data.location,
        start_time: data.start_time,
        end_time: data.end_time,
        max_capacity: data.max_capacity,
        timezone: data.timezone || 'Asia/Kolkata',
      };

      const response = await eventApi.createEvent(eventData);
      
      if (response.success) {
        reset();
        onSuccess?.(response.data);
      } else {
        setSubmitError(response.message || 'Failed to create event');
      }
    } catch (error: any) {
      setSubmitError(error.message || 'Failed to create event');
    } finally {
      setIsSubmitting(false);
    }
  };

  const formatDateTimeForInput = (date: Date) => {
    return format(date, "yyyy-MM-dd'T'HH:mm");
  };

  const getMinEndTime = () => {
    if (startTime) {
      const startDate = new Date(startTime);
      startDate.setMinutes(startDate.getMinutes() + 30); // Minimum 30 minutes duration
      return formatDateTimeForInput(startDate);
    }
    return undefined;
  };

  return (
    <div className={cn("bg-white rounded-lg shadow-md border border-gray-200 p-6", className)}>
      <h2 className="text-2xl font-bold text-gray-900 mb-6">Create New Event</h2>

      {submitError && (
        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
          {submitError}
        </div>
      )}

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
        {/* Event Name */}
        <div>
          <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-2">
            Event Name *
          </label>
          <input
            {...register('name')}
            type="text"
            id="name"
            className={cn(
              "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 placeholder-gray-500",
              errors.name && "border-red-500 focus:ring-red-500 focus:border-red-500"
            )}
            placeholder="Enter event name"
          />
          {errors.name && (
            <p className="mt-1 text-sm text-red-600">{errors.name.message}</p>
          )}
        </div>

        {/* Location */}
        <div>
          <label htmlFor="location" className="block text-sm font-medium text-gray-700 mb-2">
            Location *
          </label>
          <input
            {...register('location')}
            type="text"
            id="location"
            className={cn(
              "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 placeholder-gray-500",
              errors.location && "border-red-500 focus:ring-red-500 focus:border-red-500"
            )}
            placeholder="Enter event location"
          />
          {errors.location && (
            <p className="mt-1 text-sm text-red-600">{errors.location.message}</p>
          )}
        </div>

        {/* Date and Time Row */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {/* Start Time */}
          <div>
            <label htmlFor="start_time" className="block text-sm font-medium text-gray-700 mb-2">
              Start Date & Time *
            </label>
            <input
              {...register('start_time')}
              type="datetime-local"
              id="start_time"
              min={formatDateTimeForInput(new Date())}
              className={cn(
                "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900",
                errors.start_time && "border-red-500 focus:ring-red-500 focus:border-red-500"
              )}
            />
            {errors.start_time && (
              <p className="mt-1 text-sm text-red-600">{errors.start_time.message}</p>
            )}
          </div>

          {/* End Time */}
          <div>
            <label htmlFor="end_time" className="block text-sm font-medium text-gray-700 mb-2">
              End Date & Time *
            </label>
            <input
              {...register('end_time')}
              type="datetime-local"
              id="end_time"
              min={getMinEndTime()}
              className={cn(
                "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900",
                errors.end_time && "border-red-500 focus:ring-red-500 focus:border-red-500"
              )}
            />
            {errors.end_time && (
              <p className="mt-1 text-sm text-red-600">{errors.end_time.message}</p>
            )}
          </div>
        </div>

        {/* Capacity and Timezone Row */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {/* Max Capacity */}
          <div>
            <label htmlFor="max_capacity" className="block text-sm font-medium text-gray-700 mb-2">
              Maximum Capacity *
            </label>
            <input
              {...register('max_capacity', { valueAsNumber: true })}
              type="number"
              id="max_capacity"
              min="1"
              max="10000"
              className={cn(
                "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 placeholder-gray-500",
                errors.max_capacity && "border-red-500 focus:ring-red-500 focus:border-red-500"
              )}
              placeholder="Enter maximum attendees"
            />
            {errors.max_capacity && (
              <p className="mt-1 text-sm text-red-600">{errors.max_capacity.message}</p>
            )}
          </div>

          {/* Timezone */}
          <div>
            <label htmlFor="timezone" className="block text-sm font-medium text-gray-700 mb-2">
              Timezone
            </label>
            <select
              {...register('timezone')}
              id="timezone"
              className="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900"
            >
              <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
              <option value="America/New_York">America/New_York (EST)</option>
              <option value="Europe/London">Europe/London (GMT)</option>
              <option value="Asia/Tokyo">Asia/Tokyo (JST)</option>
              <option value="Australia/Sydney">Australia/Sydney (AEDT)</option>
            </select>
          </div>
        </div>

        {/* Action Buttons */}
        <div className="flex gap-3 pt-4">
          {onCancel && (
            <Button
              type="button"
              variant="outline"
              onClick={onCancel}
              disabled={isSubmitting}
              className="flex-1"
            >
              Cancel
            </Button>
          )}
          
          <Button
            type="submit"
            disabled={isSubmitting}
            className="flex-1 font-medium"
          >
            {isSubmitting ? (
              <>
                <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                Creating...
              </>
            ) : (
              'Create Event'
            )}
          </Button>
        </div>
      </form>
    </div>
  );
}
