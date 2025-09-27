# 🎯 Mini Event Management System

A comprehensive full-stack event management application built with **Laravel (Backend)** and **Next.js (Frontend)**, featuring clean architecture, robust testing, and complete API documentation.

## 📋 Table of Contents
- [Features](#-features)
- [Architecture](#️-architecture)
- [Prerequisites](#-prerequisites)
- [Installation & Setup](#-installation--setup)
- [API Documentation](#-api-documentation)
- [Sample API Requests](#-sample-api-requests)
- [Database Schema](#️-database-schema)
- [Testing](#-testing)
- [Assumptions & Design Decisions](#-assumptions--design-decisions)
- [Bonus Features](#-bonus-features)

## ✨ Features

### Core Requirements ✅
- **Event Creation**: Create events with name, location, start_time, end_time, max_capacity
- **Event Listing**: View all upcoming events with pagination
- **Attendee Registration**: Register attendees with name and email validation
- **Capacity Management**: Prevent overbooking (max_capacity enforcement)
- **Duplicate Prevention**: Prevent duplicate email registrations per event
- **Attendee Listing**: View all registered attendees for an event

### Bonus Features ✅
- **Pagination**: Implemented on both events and attendees lists
- **Unit Tests**: Comprehensive test suite (70 tests, 398 assertions)
- **Swagger Documentation**: Complete OpenAPI 3.0 documentation
- **Timezone Management**: IST timezone support with conversion
- **Additional Endpoints**: Event updates, deletions, attendee management
- **Real-time Validation**: Client-side and server-side validation

### Technical Excellence ✅
- **Clean Architecture**: MVC pattern with service layer
- **Separation of Concerns**: Models, Services, Controllers properly separated
- **Input Validation**: Comprehensive validation with meaningful error messages
- **Error Handling**: Proper HTTP status codes and error responses
- **Database Design**: Proper relationships and constraints
- **Code Quality**: PSR standards, meaningful naming, DRY principles

## 🏗️ Architecture

```
event-management-system/
├── backend/ (Laravel API)
│   ├── app/
│   │   ├── Http/Controllers/Api/    # API Controllers
│   │   ├── Models/                  # Eloquent Models
│   │   ├── Services/                # Business Logic Layer
│   │   └── Http/Requests/           # Form Validation
│   ├── database/
│   │   ├── migrations/              # Database Schema
│   │   └── factories/               # Test Data Factories
│   ├── tests/
│   │   ├── Unit/                    # Service Layer Tests
│   │   └── Feature/                 # API Endpoint Tests
│   └── routes/api.php               # API Routes
├── frontend/ (Next.js)
│   ├── src/
│   │   ├── app/                     # Next.js App Router
│   │   ├── components/              # React Components
│   │   └── lib/                     # Utilities & API Client
│   └── public/                      # Static Assets
└── README.md                        # This file
```

## 📋 Prerequisites

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 18.0
- **npm** or **yarn**
- **SQLite** (included with PHP)

## 🚀 Installation & Setup

### 1. Clone the Repository
```bash
git clone <repository-url>
cd event-management-system
```

### 2. Backend Setup (Laravel)
```bash
# Navigate to backend directory
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Run database migrations
php artisan migrate

# (Optional) Seed database with sample data
php artisan db:seed

# Generate Swagger documentation
php artisan l5-swagger:generate

# Start the Laravel development server
php artisan serve
# Server will run on http://localhost:8000
```

### 3. Frontend Setup (Next.js)
```bash
# Navigate to frontend directory (in a new terminal)
cd frontend

# Install Node.js dependencies
npm install

# Start the Next.js development server
npm run dev
# Frontend will run on http://localhost:3000
```

### 4. Access the Application
- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000/api
- **API Documentation**: http://localhost:8000/api/documentation
- **Health Check**: http://localhost:8000/api/health

## 📚 API Documentation

### Interactive Documentation
Access the complete Swagger/OpenAPI documentation at:
```
http://localhost:8000/api/documentation
```

### API Base URL
```
http://localhost:8000/api
```

### Authentication
Currently, the API doesn't require authentication (as per assignment requirements).

## 🔧 Sample API Requests

### 1. Health Check
```bash
curl -X GET "http://localhost:8000/api/health" \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "success": true,
  "message": "API is running",
  "timestamp": "2025-09-27T10:00:00.000000Z"
}
```

### 2. Create an Event
```bash
curl -X POST "http://localhost:8000/api/events" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Tech Conference 2025",
    "location": "Mumbai, India",
    "start_time": "2025-12-01 10:00:00",
    "end_time": "2025-12-01 18:00:00",
    "max_capacity": 100,
    "timezone": "Asia/Kolkata"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Event created successfully",
  "data": {
    "id": 1,
    "name": "Tech Conference 2025",
    "location": "Mumbai, India",
    "start_time": "2025-12-01T04:30:00.000000Z",
    "end_time": "2025-12-01T12:30:00.000000Z",
    "max_capacity": 100,
    "current_attendees": 0,
    "timezone": "Asia/Kolkata",
    "created_at": "2025-09-27T10:00:00.000000Z",
    "updated_at": "2025-09-27T10:00:00.000000Z"
  }
}
```

### 3. Get All Events
```bash
curl -X GET "http://localhost:8000/api/events?per_page=10" \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "success": true,
  "message": "Events retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Tech Conference 2025",
      "location": "Mumbai, India",
      "start_time": "2025-12-01T04:30:00.000000Z",
      "end_time": "2025-12-01T12:30:00.000000Z",
      "max_capacity": 100,
      "current_attendees": 0,
      "timezone": "Asia/Kolkata"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1,
    "from": 1,
    "to": 1
  }
}
```

### 4. Get Event Details
```bash
curl -X GET "http://localhost:8000/api/events/1" \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "success": true,
  "message": "Event retrieved successfully",
  "data": {
    "event": {
      "id": 1,
      "name": "Tech Conference 2025",
      "location": "Mumbai, India",
      "start_time": "2025-12-01T04:30:00.000000Z",
      "end_time": "2025-12-01T12:30:00.000000Z",
      "max_capacity": 100,
      "current_attendees": 0,
      "timezone": "Asia/Kolkata"
    },
    "statistics": {
      "total_capacity": 100,
      "current_attendees": 0,
      "remaining_capacity": 100,
      "capacity_percentage": 0,
      "is_full": false,
      "is_upcoming": true
    }
  }
}
```

### 5. Register Attendee
```bash
curl -X POST "http://localhost:8000/api/events/1/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john.doe@example.com"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Successfully registered for the event",
  "data": {
    "id": 1,
    "event_id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "created_at": "2025-09-27T10:00:00.000000Z",
    "updated_at": "2025-09-27T10:00:00.000000Z",
    "event": {
      "id": 1,
      "name": "Tech Conference 2025",
      "location": "Mumbai, India"
    }
  }
}
```

### 6. Get Event Attendees
```bash
curl -X GET "http://localhost:8000/api/events/1/attendees?per_page=10" \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "success": true,
  "message": "Attendees retrieved successfully",
  "data": [
    {
      "id": 1,
      "event_id": 1,
      "name": "John Doe",
      "email": "john.doe@example.com",
      "created_at": "2025-09-27T10:00:00.000000Z",
      "updated_at": "2025-09-27T10:00:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1,
    "from": 1,
    "to": 1
  }
}
```

### 7. Search Attendees
```bash
curl -X GET "http://localhost:8000/api/events/1/attendees?search=john" \
  -H "Accept: application/json"
```

### 8. Check Registration Status
```bash
curl -X GET "http://localhost:8000/api/events/1/attendees/check/john.doe@example.com" \
  -H "Accept: application/json"
```

### 9. Get Attendee Count
```bash
curl -X GET "http://localhost:8000/api/events/1/attendees/count" \
  -H "Accept: application/json"
```

### 10. Update Event
```bash
curl -X PUT "http://localhost:8000/api/events/1" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Updated Tech Conference 2025",
    "max_capacity": 150
  }'
```

### 11. Delete Event
```bash
curl -X DELETE "http://localhost:8000/api/events/1" \
  -H "Accept: application/json"
```

## 🗄️ Database Schema

### Tables Structure

#### `events` Table
```sql
CREATE TABLE events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    max_capacity INTEGER NOT NULL,
    current_attendees INTEGER DEFAULT 0,
    timezone VARCHAR(255) DEFAULT 'Asia/Kolkata',
    created_at DATETIME,
    updated_at DATETIME
);
```

#### `attendees` Table
```sql
CREATE TABLE attendees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_id INTEGER NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE(event_id, email)
);
```

### Relationships
- **One-to-Many**: Event → Attendees
- **Constraints**: 
  - Unique email per event (prevents duplicates)
  - Cascade delete (removing event removes attendees)
  - Foreign key integrity

### Migrations
Located in `backend/database/migrations/`:
- `2025_09_27_094014_create_events_table.php`
- `2025_09_27_094021_create_attendees_table.php`

## 🧪 Testing

### Running Tests
```bash
cd backend

# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

### Test Coverage
- **Total Tests**: 70
- **Unit Tests**: 36 (EventService: 16, AttendeeService: 20)
- **Feature Tests**: 32 (EventController: 14, AttendeeController: 18)
- **Assertions**: 398
- **Pass Rate**: 100%

### Test Categories
- ✅ Service layer business logic
- ✅ API endpoint functionality
- ✅ Validation and error handling
- ✅ Edge cases and boundary conditions
- ✅ Database operations and relationships

## 💡 Assumptions & Design Decisions

### Timezone Management
- **Default Timezone**: Asia/Kolkata (IST)
- **Storage**: All times stored as UTC in database
- **Conversion**: Automatic conversion from user timezone to UTC
- **Display**: Times can be displayed in user's preferred timezone

### Capacity Management
- **Real-time Tracking**: `current_attendees` field updated on registration/removal
- **Atomic Operations**: Database transactions prevent race conditions
- **Validation**: Server-side capacity checks before registration

### Email Uniqueness
- **Per Event**: Same email can register for different events
- **Database Constraint**: Unique constraint on (event_id, email)
- **User Experience**: Clear error messages for duplicate registrations

### API Design
- **RESTful**: Following REST conventions
- **Consistent Responses**: Standardized success/error response format
- **Pagination**: Implemented for scalability
- **Validation**: Both client-side and server-side validation

### Data Integrity
- **Foreign Keys**: Proper relationships with cascade deletes
- **Transactions**: Database transactions for multi-step operations
- **Validation**: Comprehensive input validation and sanitization

## 🎁 Bonus Features

### ✅ Implemented
1. **Pagination**: Both events and attendees lists
2. **Unit Tests**: Comprehensive test suite (70 tests)
3. **Swagger Documentation**: Complete OpenAPI 3.0 specs
4. **Additional Endpoints**: CRUD operations for events
5. **Search Functionality**: Search attendees by name/email
6. **Attendee Management**: Remove attendees, check registration status
7. **Statistics**: Event capacity statistics and analytics
8. **Error Handling**: Comprehensive error responses
9. **Factory Classes**: Test data generation
10. **Service Layer**: Clean separation of business logic

### 📁 Project Structure
```
event-management-system/
├── backend/                         # Laravel API Backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Api/
│   │   │   │   │   ├── EventController.php
│   │   │   │   │   ├── AttendeeController.php
│   │   │   │   │   └── SchemaDefinitions.php
│   │   │   │   └── Controller.php
│   │   │   └── Requests/
│   │   │       ├── CreateEventRequest.php
│   │   │       └── UpdateEventRequest.php
│   │   ├── Models/
│   │   │   ├── Event.php
│   │   │   └── Attendee.php
│   │   └── Services/
│   │       ├── EventService.php
│   │       └── AttendeeService.php
│   ├── database/
│   │   ├── factories/
│   │   │   ├── EventFactory.php
│   │   │   └── AttendeeFactory.php
│   │   ├── migrations/
│   │   │   ├── 2025_09_27_094014_create_events_table.php
│   │   │   └── 2025_09_27_094021_create_attendees_table.php
│   │   └── database.sqlite
│   ├── tests/
│   │   ├── Unit/
│   │   │   ├── EventServiceTest.php
│   │   │   └── AttendeeServiceTest.php
│   │   └── Feature/
│   │       ├── EventControllerTest.php
│   │       └── AttendeeControllerTest.php
│   ├── routes/api.php
│   ├── config/l5-swagger.php
│   └── TESTING_AND_DOCS.md
├── frontend/                        # Next.js Frontend
│   ├── src/
│   │   ├── app/
│   │   │   ├── page.tsx
│   │   │   └── layout.tsx
│   │   ├── components/
│   │   │   ├── EventCard.tsx
│   │   │   ├── EventForm.tsx
│   │   │   ├── AttendeeList.tsx
│   │   │   └── RegistrationForm.tsx
│   │   └── lib/
│   │       ├── api.ts
│   │       ├── utils.ts
│   │       └── timezone.ts
│   └── package.json
└── README.md                        # This comprehensive guide
```

## 🚀 Deployment Notes

### Environment Variables
```env
# Backend (.env)
APP_NAME="Event Management System"
APP_ENV=production
APP_KEY=base64:generated_key
APP_DEBUG=false
APP_URL=https://your-api-domain.com

DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite

L5_SWAGGER_CONST_HOST=https://your-api-domain.com
```

### Frontend Environment
```env
# Frontend (.env.local)
NEXT_PUBLIC_API_URL=https://your-api-domain.com/api
```

## 📞 Support

For any issues or questions:
1. Check the **API Documentation** at `/api/documentation`
2. Review the **Test Suite** for usage examples
3. Check **Laravel Logs** at `backend/storage/logs/laravel.log`

---

**Built with ❤️ using Laravel, Next.js, and modern web technologies.**
