import axios from 'axios';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

// Create axios instance
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor
api.interceptors.request.use(
  (config) => {
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor for error handling
api.interceptors.response.use(
  (response) => {
    return response.data;
  },
  (error) => {
    const errorMessage = error.response?.data?.message || error.message || 'Something went wrong';
    return Promise.reject({
      message: errorMessage,
      status: error.response?.status,
      data: error.response?.data
    });
  }
);

// Event types
export interface Event {
  id: number;
  name: string;
  location: string;
  start_time: string;
  end_time: string;
  max_capacity: number;
  current_attendees: number;
  timezone: string;
  created_at: string;
  updated_at: string;
  attendees?: Attendee[];
}

export interface Attendee {
  id: number;
  event_id: number;
  name: string;
  email: string;
  created_at: string;
  updated_at: string;
}

export interface CreateEventData {
  name: string;
  location: string;
  start_time: string;
  end_time: string;
  max_capacity: number;
  timezone?: string;
}

export interface RegisterAttendeeData {
  name: string;
  email: string;
}

export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data: T;
}

// API functions
export const eventApi = {
  // Get all events
  getEvents: (): Promise<ApiResponse<Event[]>> => 
    api.get('/events'),

  // Get single event
  getEvent: (id: number): Promise<ApiResponse<Event>> => 
    api.get(`/events/${id}`),

  // Create event
  createEvent: (data: CreateEventData): Promise<ApiResponse<Event>> => 
    api.post('/events', data),

  // Update event
  updateEvent: (id: number, data: Partial<CreateEventData>): Promise<ApiResponse<Event>> => 
    api.put(`/events/${id}`, data),

  // Delete event
  deleteEvent: (id: number): Promise<ApiResponse<void>> => 
    api.delete(`/events/${id}`),

  // Register attendee
  registerAttendee: (eventId: number, data: RegisterAttendeeData): Promise<ApiResponse<Attendee>> => 
    api.post(`/events/${eventId}/register`, data),

  // Get event attendees
  getEventAttendees: (eventId: number, params?: { page?: number; per_page?: number; search?: string }): Promise<ApiResponse<Attendee[]>> => 
    api.get(`/events/${eventId}/attendees`, { params }),

  // Unregister attendee
  unregisterAttendee: (eventId: number, attendeeId: number): Promise<ApiResponse<void>> => 
    api.delete(`/events/${eventId}/attendees/${attendeeId}`),
};

export default api;
