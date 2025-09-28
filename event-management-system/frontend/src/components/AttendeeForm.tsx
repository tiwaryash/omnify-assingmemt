'use client';

import React, { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Loader2, User, Mail } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { RegisterAttendeeData, eventApi, Event } from '@/lib/api';
import { cn } from '@/lib/utils';

// Validation schema
const attendeeSchema = z.object({
  name: z.string().min(1, 'Name is required').max(255, 'Name is too long'),
  email: z.string().email('Please enter a valid email address').max(255, 'Email is too long'),
});

type AttendeeFormData = z.infer<typeof attendeeSchema>;

interface AttendeeFormProps {
  event: Event;
  onSuccess?: (attendee: unknown) => void;
  onCancel?: () => void;
  className?: string;
}

export function AttendeeForm({ event, onSuccess, onCancel, className }: AttendeeFormProps) {
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    formState: { errors },
    reset,
  } = useForm<AttendeeFormData>({
    resolver: zodResolver(attendeeSchema),
  });

  const isFullyBooked = event.current_attendees >= event.max_capacity;
  const availableSpots = event.max_capacity - event.current_attendees;

  const onSubmit = async (data: AttendeeFormData) => {
    if (isFullyBooked) {
      setSubmitError('This event is fully booked');
      return;
    }

    setIsSubmitting(true);
    setSubmitError(null);
    setSuccessMessage(null);

    try {
      const attendeeData: RegisterAttendeeData = {
        name: data.name,
        email: data.email,
      };

      const response = await eventApi.registerAttendee(event.id, attendeeData);
      
      if (response.success) {
        setSuccessMessage(`Successfully registered for "${event.name}"! You should receive a confirmation email shortly.`);
        reset();
        onSuccess?.(response.data);
      } else {
        setSubmitError(response.message || 'Failed to register for event');
      }
    } catch (error: unknown) {
      setSubmitError(error instanceof Error ? error.message : 'Failed to register for event');
    } finally {
      setIsSubmitting(false);
    }
  };

  if (successMessage) {
    return (
      <div className={cn("bg-white rounded-lg shadow-md border border-gray-200 p-6", className)}>
        <div className="text-center">
          <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg className="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h3 className="text-lg font-semibold text-gray-900 mb-2">Registration Successful!</h3>
          <p className="text-gray-600 mb-6">{successMessage}</p>
          <Button onClick={onCancel} variant="outline">
            Close
          </Button>
        </div>
      </div>
    );
  }

  return (
    <div className={cn("bg-white rounded-lg shadow-md border border-gray-200 p-6", className)}>
      <div className="mb-6">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">Register for Event</h2>
        <h3 className="text-lg text-gray-700 mb-4">{event.name}</h3>
        
        {/* Event Info */}
        <div className="bg-gray-50 rounded-lg p-4 mb-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
              <span className="font-medium text-gray-700">Location:</span>
              <span className="ml-2 text-gray-600">{event.location}</span>
            </div>
            <div>
              <span className="font-medium text-gray-700">Capacity:</span>
              <span className="ml-2 text-gray-600">
                {event.current_attendees} / {event.max_capacity} registered
              </span>
            </div>
          </div>
        </div>

        {/* Availability Status */}
        <div className={cn(
          "flex items-center justify-between p-3 rounded-lg mb-4",
          isFullyBooked 
            ? "bg-red-50 border border-red-200" 
            : availableSpots <= 5 
              ? "bg-yellow-50 border border-yellow-200"
              : "bg-green-50 border border-green-200"
        )}>
          <span className={cn(
            "font-medium",
            isFullyBooked 
              ? "text-red-700" 
              : availableSpots <= 5 
                ? "text-yellow-700"
                : "text-green-700"
          )}>
            {isFullyBooked 
              ? "Event is fully booked" 
              : `${availableSpots} spot${availableSpots !== 1 ? 's' : ''} remaining`
            }
          </span>
          {availableSpots <= 5 && !isFullyBooked && (
            <span className="text-sm text-yellow-600">Filling up fast!</span>
          )}
        </div>
      </div>

      {isFullyBooked ? (
        <div className="text-center py-8">
          <p className="text-gray-600 mb-4">
            Unfortunately, this event has reached its maximum capacity.
          </p>
          <Button onClick={onCancel} variant="outline">
            Close
          </Button>
        </div>
      ) : (
        <>
          {submitError && (
            <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
              {submitError}
            </div>
          )}

          <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
            {/* Name */}
            <div>
              <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-2">
                Full Name *
              </label>
              <div className="relative">
                <User className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                  {...register('name')}
                  type="text"
                  id="name"
                  className={cn(
                    "w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 placeholder-gray-500",
                    errors.name && "border-red-500 focus:ring-red-500 focus:border-red-500"
                  )}
                  placeholder="Enter your full name"
                />
              </div>
              {errors.name && (
                <p className="mt-1 text-sm text-red-600">{errors.name.message}</p>
              )}
            </div>

            {/* Email */}
            <div>
              <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
                Email Address *
              </label>
              <div className="relative">
                <Mail className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                  {...register('email')}
                  type="email"
                  id="email"
                  className={cn(
                    "w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 placeholder-gray-500",
                    errors.email && "border-red-500 focus:ring-red-500 focus:border-red-500"
                  )}
                  placeholder="Enter your email address"
                />
              </div>
              {errors.email && (
                <p className="mt-1 text-sm text-red-600">{errors.email.message}</p>
              )}
              <p className="mt-1 text-xs text-gray-500">
                We&apos;ll use this email to send you event updates and confirmations.
              </p>
            </div>

            {/* Terms */}
            <div className="bg-gray-50 rounded-lg p-4">
              <p className="text-sm text-gray-600">
                By registering for this event, you agree to receive email notifications 
                about this event and understand that your information will be shared 
                with the event organizer.
              </p>
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
                disabled={isSubmitting || isFullyBooked}
                className="flex-1 font-medium"
              >
                {isSubmitting ? (
                  <>
                    <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                    Registering...
                  </>
                ) : (
                  'Register for Event'
                )}
              </Button>
            </div>
          </form>
        </>
      )}
    </div>
  );
}
