'use client';

import React, { useState, useEffect } from 'react';
import { Plus, Search, Calendar, Filter } from 'lucide-react';
import { Event, eventApi } from '@/lib/api';
import { EventCard } from './EventCard';
import { EventForm } from './EventForm';
import { AttendeeForm } from './AttendeeForm';
import { AttendeesModal } from './AttendeesModal';
import { TimezoneSelector } from './TimezoneSelector';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { getUserTimezone } from '@/lib/timezone';

type ViewMode = 'list' | 'create' | 'register';

interface EventsListProps {
  className?: string;
}

export function EventsList({ className }: EventsListProps) {
  const [events, setEvents] = useState<Event[]>([]);
  const [filteredEvents, setFilteredEvents] = useState<Event[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [viewMode, setViewMode] = useState<ViewMode>('list');
  const [selectedEvent, setSelectedEvent] = useState<Event | null>(null);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterStatus, setFilterStatus] = useState<'all' | 'available' | 'full'>('all');
  const [showAttendeesModal, setShowAttendeesModal] = useState(false);
  const [selectedTimezone, setSelectedTimezone] = useState('Asia/Kolkata');

  // Fetch events
  const fetchEvents = async () => {
    try {
      setLoading(true);
      setError(null);
      const response = await eventApi.getEvents();
      
      if (response.success && Array.isArray(response.data)) {
        setEvents(response.data);
        setFilteredEvents(response.data);
      } else {
        setError('Failed to load events');
      }
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Failed to load events');
    } finally {
      setLoading(false);
    }
  };

  // Filter events based on search and status
  useEffect(() => {
    let filtered = events;

    // Apply search filter
    if (searchTerm) {
      filtered = filtered.filter(event =>
        event.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        event.location.toLowerCase().includes(searchTerm.toLowerCase())
      );
    }

    // Apply status filter
    if (filterStatus === 'available') {
      filtered = filtered.filter(event => event.current_attendees < event.max_capacity);
    } else if (filterStatus === 'full') {
      filtered = filtered.filter(event => event.current_attendees >= event.max_capacity);
    }

    setFilteredEvents(filtered);
  }, [events, searchTerm, filterStatus]);

  // Load events on component mount and detect user timezone
  useEffect(() => {
    fetchEvents();
    // Try to detect user's timezone
    const userTz = getUserTimezone();
    setSelectedTimezone(userTz);
  }, []);

  const handleEventCreated = (newEvent: Event) => {
    setEvents(prev => [newEvent, ...prev]);
    setViewMode('list');
    window.location.reload();
  };

  const handleRegistrationSuccess = () => {
    // Refresh events to get updated attendee count
    fetchEvents();
    setViewMode('list');
    setSelectedEvent(null);
  };

  const handleRegisterClick = (event: Event) => {
    setSelectedEvent(event);
    setViewMode('register');
  };

  const handleViewDetails = (event: Event) => {
    setSelectedEvent(event);
    setShowAttendeesModal(true);
  };

  const handleBackToList = () => {
    setViewMode('list');
    setSelectedEvent(null);
  };

  // Render create event form
  if (viewMode === 'create') {
    return (
      <div className={cn("max-w-2xl mx-auto", className)}>
        <EventForm
          onSuccess={handleEventCreated}
          onCancel={handleBackToList}
        />
      </div>
    );
  }

  // Render registration form
  if (viewMode === 'register' && selectedEvent) {
    return (
      <div className={cn("max-w-2xl mx-auto", className)}>
        <AttendeeForm
          event={selectedEvent}
          onSuccess={handleRegistrationSuccess}
          onCancel={handleBackToList}
        />
      </div>
    );
  }

  // Render events list
  return (
    <div className={cn("space-y-6", className)}>
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Events</h1>
          <p className="text-gray-600">Discover and register for upcoming events</p>
        </div>
        <div className="flex flex-col sm:flex-row gap-3 items-center">
          <TimezoneSelector
            selectedTimezone={selectedTimezone}
            onTimezoneChange={setSelectedTimezone}
          />
        <Button 
          onClick={() => setViewMode('create')} 
          className="flex items-center gap-2 font-medium"
        >
          <Plus className="h-4 w-4" />
          Create Event
        </Button>
        </div>
      </div>

      {/* Search and Filter Bar */}
      <div className="flex flex-col sm:flex-row gap-4">
        {/* Search */}
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
          <input
            type="text"
            placeholder="Search events by name or location..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 placeholder-gray-500"
          />
        </div>

        {/* Filter */}
        <div className="flex items-center gap-2">
          <Filter className="h-4 w-4 text-gray-400" />
          <select
            value={filterStatus}
            onChange={(e) => setFilterStatus(e.target.value as 'all' | 'available' | 'full')}
            className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900"
          >
            <option value="all">All Events</option>
            <option value="available">Available</option>
            <option value="full">Fully Booked</option>
          </select>
        </div>
      </div>

      {/* Content */}
      {loading ? (
        <div className="flex justify-center items-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <span className="ml-3 text-gray-600">Loading events...</span>
        </div>
      ) : error ? (
        <div className="text-center py-12">
          <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg inline-block">
            <p className="font-medium">Error loading events</p>
            <p className="text-sm mt-1">{error}</p>
          </div>
          <Button 
            onClick={fetchEvents} 
            variant="outline" 
            className="mt-4"
          >
            Try Again
          </Button>
        </div>
      ) : filteredEvents.length === 0 ? (
        <div className="text-center py-12">
          {events.length === 0 ? (
            <div>
              <Calendar className="h-12 w-12 text-gray-400 mx-auto mb-4" />
              <h3 className="text-lg font-medium text-gray-900 mb-2">No events yet</h3>
              <p className="text-gray-600 mb-4">Get started by creating your first event.</p>
              <Button 
                onClick={() => setViewMode('create')}
                className="font-medium"
              >
                <Plus className="h-4 w-4 mr-2" />
                Create Event
              </Button>
            </div>
          ) : (
            <div>
              <Search className="h-12 w-12 text-gray-400 mx-auto mb-4" />
              <h3 className="text-lg font-medium text-gray-900 mb-2">No events found</h3>
              <p className="text-gray-600 mb-4">
                Try adjusting your search terms or filters.
              </p>
              <div className="flex justify-center gap-2">
                <Button 
                  variant="outline" 
                  onClick={() => {
                    setSearchTerm('');
                    setFilterStatus('all');
                  }}
                >
                  Clear Filters
                </Button>
                <Button 
                  onClick={() => setViewMode('create')}
                  className="font-medium"
                >
                  Create Event
                </Button>
              </div>
            </div>
          )}
        </div>
      ) : (
        <>
          {/* Results Info */}
          <div className="flex items-center justify-between text-sm text-gray-600">
            <span>
              Showing {filteredEvents.length} of {events.length} event{events.length !== 1 ? 's' : ''}
            </span>
            {(searchTerm || filterStatus !== 'all') && (
              <Button 
                variant="ghost" 
                size="sm"
                onClick={() => {
                  setSearchTerm('');
                  setFilterStatus('all');
                }}
              >
                Clear filters
              </Button>
            )}
          </div>

          {/* Events Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {filteredEvents.map((event) => (
              <EventCard
                key={event.id}
                event={event}
                onRegister={handleRegisterClick}
                onViewDetails={handleViewDetails}
                displayTimezone={selectedTimezone}
              />
            ))}
          </div>
        </>
      )}

      {/* Attendees Modal */}
      {selectedEvent && (
        <AttendeesModal
          event={selectedEvent}
          isOpen={showAttendeesModal}
          onClose={() => {
            setShowAttendeesModal(false);
            setSelectedEvent(null);
          }}
        />
      )}
    </div>
  );
}
