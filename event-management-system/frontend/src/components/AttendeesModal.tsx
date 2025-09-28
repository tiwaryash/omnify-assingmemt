'use client';

import React, { useState, useEffect, useCallback } from 'react';
import { X, Users, Mail, User, Loader2, Search, ChevronLeft, ChevronRight } from 'lucide-react';
import { Event, Attendee, eventApi } from '@/lib/api';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

interface AttendeesModalProps {
  event: Event;
  isOpen: boolean;
  onClose: () => void;
}

interface PaginationInfo {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
}

export function AttendeesModal({ event, isOpen, onClose }: AttendeesModalProps) {
  const [attendees, setAttendees] = useState<Attendee[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [searchTerm, setSearchTerm] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [pagination, setPagination] = useState<PaginationInfo | null>(null);
  const [perPage, setPerPage] = useState(10);

  const fetchAttendees = useCallback(async (page = 1, search = '') => {
    setLoading(true);
    setError(null);
    
    try {
      const params: { page: number; per_page: number; search?: string } = { page, per_page: perPage };
      if (search.trim()) {
        params.search = search.trim();
      }

      const response = await eventApi.getEventAttendees(event.id, params);
      if (response.success) {
        setAttendees(response.data);
        // The backend returns pagination info
        if ('pagination' in response) {
          setPagination(response.pagination as PaginationInfo);
        }
      } else {
        setError(response.message || 'Failed to load attendees');
      }
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Failed to load attendees');
    } finally {
      setLoading(false);
    }
  }, [event.id, perPage]);

  useEffect(() => {
    if (isOpen) {
      fetchAttendees(1, searchTerm);
      setCurrentPage(1);
    }
  }, [isOpen, fetchAttendees, searchTerm]);

  const handleSearch = (value: string) => {
    setSearchTerm(value);
    setCurrentPage(1);
  };

  const handlePageChange = (page: number) => {
    setCurrentPage(page);
    fetchAttendees(page, searchTerm);
  };

  const handlePerPageChange = (newPerPage: number) => {
    setPerPage(newPerPage);
    setCurrentPage(1);
    fetchAttendees(1, searchTerm);
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      {/* Backdrop */}
      <div 
        className="absolute inset-0 bg-black bg-opacity-50"
        onClick={onClose}
      />
      
      {/* Modal */}
      <div className="relative bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] flex flex-col">
        {/* Header */}
        <div className="flex items-center justify-between p-6 border-b border-gray-200">
          <div>
            <h2 className="text-xl font-semibold text-gray-900">Event Attendees</h2>
            <p className="text-sm text-gray-600 mt-1">{event.name}</p>
          </div>
          <button
            onClick={onClose}
            className="p-2 hover:bg-gray-100 rounded-full transition-colors"
          >
            <X className="h-5 w-5 text-gray-500" />
          </button>
        </div>

        {/* Content */}
        <div className="flex-1 overflow-y-auto p-6">
          {/* Event Info */}
          <div className="bg-gray-50 rounded-lg p-4 mb-6">
            <div className="flex items-center justify-between text-sm">
              <div className="flex items-center">
                <Users className="h-4 w-4 text-gray-500 mr-2" />
                <span className="font-medium text-gray-700">
                  {event.current_attendees} / {event.max_capacity} registered
                </span>
              </div>
              <div className="text-gray-600">
                {event.max_capacity - event.current_attendees} spots remaining
              </div>
            </div>
            
            {/* Progress Bar */}
            <div className="w-full bg-gray-200 rounded-full h-2 mt-3">
              <div 
                className={cn(
                  "h-2 rounded-full transition-all duration-300",
                  event.current_attendees >= event.max_capacity 
                    ? "bg-red-500" 
                    : event.max_capacity - event.current_attendees <= 5 
                      ? "bg-yellow-500"
                      : "bg-green-500"
                )}
                style={{ 
                  width: `${Math.min((event.current_attendees / event.max_capacity) * 100, 100)}%` 
                }}
              />
            </div>
          </div>

          {/* Search and Controls */}
          <div className="flex flex-col sm:flex-row gap-4 mb-6">
            {/* Search Bar */}
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
              <input
                type="text"
                placeholder="Search attendees by name or email..."
                value={searchTerm}
                onChange={(e) => handleSearch(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 placeholder-gray-500"
              />
            </div>

            {/* Items Per Page */}
            <div className="flex items-center gap-2">
              <span className="text-sm text-gray-600">Show:</span>
              <select
                value={perPage}
                onChange={(e) => handlePerPageChange(Number(e.target.value))}
                className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-gray-900 text-sm"
              >
                <option value={5}>5</option>
                <option value={10}>10</option>
                <option value={20}>20</option>
                <option value={50}>50</option>
              </select>
            </div>
          </div>

          {/* Loading State */}
          {loading && (
            <div className="flex items-center justify-center py-8">
              <Loader2 className="h-6 w-6 animate-spin text-blue-600 mr-3" />
              <span className="text-gray-600">Loading attendees...</span>
            </div>
          )}

          {/* Error State */}
          {error && (
            <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
              <p className="font-medium">Error loading attendees</p>
              <p className="text-sm mt-1">{error}</p>
              <Button 
                onClick={() => fetchAttendees(currentPage, searchTerm)} 
                variant="outline" 
                size="sm" 
                className="mt-2"
              >
                Try Again
              </Button>
            </div>
          )}

          {/* Attendees List */}
          {!loading && !error && (
            <>
              {attendees.length === 0 ? (
                <div className="text-center py-8">
                  <Users className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                  <h3 className="text-lg font-medium text-gray-900 mb-2">
                    {searchTerm ? 'No attendees found' : 'No attendees yet'}
                  </h3>
                  <p className="text-gray-600">
                    {searchTerm 
                      ? `No attendees match "${searchTerm}". Try a different search term.`
                      : 'Be the first to register for this event!'
                    }
                  </p>
                  {searchTerm && (
                    <Button 
                      onClick={() => handleSearch('')} 
                      variant="outline" 
                      size="sm" 
                      className="mt-3"
                    >
                      Clear Search
                    </Button>
                  )}
                </div>
              ) : (
                <div className="space-y-3">
                  <div className="flex items-center justify-between mb-4">
                    <h3 className="text-lg font-medium text-gray-900">
                      Registered Attendees
                      {pagination && (
                        <span className="text-sm font-normal text-gray-600 ml-2">
                          ({pagination.total} total)
                        </span>
                      )}
                    </h3>
                    {pagination && (
                      <div className="text-sm text-gray-600">
                        Showing {pagination.from}-{pagination.to} of {pagination.total}
                      </div>
                    )}
                  </div>
                  
                  {attendees.map((attendee, index) => (
                    <div
                      key={attendee.id}
                      className="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow"
                    >
                      <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-3">
                          <div className="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <User className="h-5 w-5 text-blue-600" />
                          </div>
                          <div>
                            <h4 className="font-medium text-gray-900">{attendee.name}</h4>
                            <div className="flex items-center text-sm text-gray-600">
                              <Mail className="h-3 w-3 mr-1" />
                              {attendee.email}
                            </div>
                          </div>
                        </div>
                        <div className="text-sm text-gray-500">
                          #{pagination ? (pagination.from || 0) + index : index + 1}
                        </div>
                      </div>
                      
                      {/* Registration Date */}
                      <div className="mt-2 text-xs text-gray-500">
                        Registered: {new Date(attendee.created_at).toLocaleDateString('en-US', {
                          year: 'numeric',
                          month: 'short',
                          day: 'numeric',
                          hour: '2-digit',
                          minute: '2-digit'
                        })}
                      </div>
                    </div>
                  ))}

                  {/* Pagination Controls */}
                  {pagination && pagination.last_page > 1 && (
                    <div className="flex items-center justify-between pt-4 border-t border-gray-200 mt-6">
                      <div className="flex items-center gap-2">
                        <Button
                          onClick={() => handlePageChange(currentPage - 1)}
                          disabled={currentPage <= 1}
                          variant="outline"
                          size="sm"
                          className="flex items-center gap-1"
                        >
                          <ChevronLeft className="h-4 w-4" />
                          Previous
                        </Button>
                        
                        <div className="flex items-center gap-1">
                          {Array.from({ length: Math.min(5, pagination.last_page) }, (_, i) => {
                            let pageNum;
                            if (pagination.last_page <= 5) {
                              pageNum = i + 1;
                            } else if (currentPage <= 3) {
                              pageNum = i + 1;
                            } else if (currentPage >= pagination.last_page - 2) {
                              pageNum = pagination.last_page - 4 + i;
                            } else {
                              pageNum = currentPage - 2 + i;
                            }

                            return (
                              <Button
                                key={pageNum}
                                onClick={() => handlePageChange(pageNum)}
                                variant={currentPage === pageNum ? "default" : "outline"}
                                size="sm"
                                className="w-8 h-8 p-0"
                              >
                                {pageNum}
                              </Button>
                            );
                          })}
                        </div>

                        <Button
                          onClick={() => handlePageChange(currentPage + 1)}
                          disabled={currentPage >= pagination.last_page}
                          variant="outline"
                          size="sm"
                          className="flex items-center gap-1"
                        >
                          Next
                          <ChevronRight className="h-4 w-4" />
                        </Button>
                      </div>

                      <div className="text-sm text-gray-600">
                        Page {pagination.current_page} of {pagination.last_page}
                      </div>
                    </div>
                  )}
                </div>
              )}
            </>
          )}
        </div>

        {/* Footer */}
        <div className="border-t border-gray-200 p-6">
          <div className="flex justify-between items-center">
            <div className="text-sm text-gray-600">
              {pagination ? (
                <span>
                  {searchTerm ? `Search results: ` : ''}
                  {pagination.total} total attendee{pagination.total !== 1 ? 's' : ''}
                  {pagination.last_page > 1 && (
                    <span> • Page {pagination.current_page} of {pagination.last_page}</span>
                  )}
                </span>
              ) : attendees.length > 0 ? (
                <span>Showing {attendees.length} attendee{attendees.length !== 1 ? 's' : ''}</span>
              ) : null}
            </div>
            <Button onClick={onClose} variant="outline">
              Close
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
}
