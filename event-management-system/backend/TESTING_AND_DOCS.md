# Event Management System - Testing & Documentation

This document provides comprehensive information about the testing suite and API documentation for the Event Management System.

## 🧪 Testing Suite

### Overview
The application includes a comprehensive testing suite with both **Unit Tests** and **Feature Tests** covering all major functionalities.

### Test Structure

```
tests/
├── Unit/
│   ├── EventServiceTest.php      # Tests for EventService business logic
│   └── AttendeeServiceTest.php   # Tests for AttendeeService business logic
├── Feature/
│   ├── EventControllerTest.php   # API endpoint tests for events
│   └── AttendeeControllerTest.php # API endpoint tests for attendees
└── TestCase.php                  # Base test class
```

### Running Tests

#### Run All Tests
```bash
php artisan test
```

#### Run Specific Test Suites
```bash
# Run only unit tests
php artisan test --testsuite=Unit

# Run only feature tests
php artisan test --testsuite=Feature

# Run specific test class
php artisan test --filter EventServiceTest
php artisan test --filter AttendeeControllerTest
```

#### Run Tests with Coverage
```bash
php artisan test --coverage
```

### Test Coverage

#### Unit Tests (EventServiceTest)
- ✅ Get upcoming events (collection and paginated)
- ✅ Create events with timezone conversion
- ✅ Get event by ID
- ✅ Update events
- ✅ Delete events
- ✅ Check available capacity
- ✅ Filter events by location
- ✅ Filter events by date range
- ✅ Get event statistics
- ✅ Handle edge cases (nonexistent events, etc.)

#### Unit Tests (AttendeeServiceTest)
- ✅ Register attendees successfully
- ✅ Handle registration failures (past events, full capacity, duplicate emails)
- ✅ Get event attendees (collection and paginated)
- ✅ Remove attendees
- ✅ Check email registration status
- ✅ Search attendees by name/email
- ✅ Get attendee count
- ✅ Handle edge cases and error conditions

#### Feature Tests (EventControllerTest)
- ✅ GET `/api/events` - List events with pagination
- ✅ POST `/api/events` - Create new events
- ✅ GET `/api/events/{id}` - Get event with statistics
- ✅ PUT `/api/events/{id}` - Update events
- ✅ DELETE `/api/events/{id}` - Delete events
- ✅ Validation error handling
- ✅ Timezone conversion testing
- ✅ Error response testing

#### Feature Tests (AttendeeControllerTest)
- ✅ POST `/api/events/{id}/register` - Register attendees
- ✅ GET `/api/events/{id}/attendees` - List attendees with search
- ✅ DELETE `/api/events/{id}/attendees/{attendee_id}` - Remove attendees
- ✅ GET `/api/events/{id}/attendees/check/{email}` - Check registration
- ✅ GET `/api/events/{id}/attendees/count` - Get attendee count
- ✅ Validation and error handling
- ✅ Concurrent registration handling

### Test Database Setup

Tests use SQLite in-memory database for fast execution:
- Database is automatically migrated before tests
- `RefreshDatabase` trait ensures clean state for each test
- Factory classes generate realistic test data

### Factories

#### EventFactory
```php
// Create upcoming event
Event::factory()->upcoming()->create();

// Create past event
Event::factory()->past()->create();

// Create full capacity event
Event::factory()->full()->create();
```

#### AttendeeFactory
```php
// Create attendee for specific event
Attendee::factory()->forEvent($event)->create();
```

## 📚 API Documentation (Swagger)

### Overview
The API is fully documented using **Swagger/OpenAPI 3.0** specifications with interactive documentation.

### Accessing Documentation

#### Local Development
```
http://localhost:8000/api/documentation
```

#### Production
```
https://your-domain.com/api/documentation
```

### Documentation Features

- **Interactive API Explorer** - Test endpoints directly from the browser
- **Request/Response Examples** - See sample data for all endpoints
- **Schema Definitions** - Detailed model specifications
- **Authentication Info** - Security requirements (when applicable)
- **Error Response Documentation** - Standard error formats

### API Endpoints Documented

#### Events
- `GET /api/events` - List upcoming events
- `POST /api/events` - Create new event
- `GET /api/events/{id}` - Get event details
- `PUT /api/events/{id}` - Update event
- `DELETE /api/events/{id}` - Delete event

#### Attendees
- `POST /api/events/{id}/register` - Register for event
- `GET /api/events/{id}/attendees` - List event attendees
- `DELETE /api/events/{id}/attendees/{attendee_id}` - Remove attendee
- `GET /api/events/{id}/attendees/check/{email}` - Check registration
- `GET /api/events/{id}/attendees/count` - Get attendee count

#### System
- `GET /api/health` - Health check endpoint

### Schema Definitions

#### Event Model
```json
{
  "id": 1,
  "name": "Tech Conference 2025",
  "location": "Mumbai, India",
  "start_time": "2025-12-01T10:00:00.000000Z",
  "end_time": "2025-12-01T18:00:00.000000Z",
  "max_capacity": 100,
  "current_attendees": 25,
  "timezone": "Asia/Kolkata",
  "created_at": "2025-09-27T10:00:00.000000Z",
  "updated_at": "2025-09-27T10:00:00.000000Z"
}
```

#### Attendee Model
```json
{
  "id": 1,
  "event_id": 1,
  "name": "John Doe",
  "email": "john.doe@example.com",
  "created_at": "2025-09-27T10:00:00.000000Z",
  "updated_at": "2025-09-27T10:00:00.000000Z"
}
```

### Regenerating Documentation

```bash
# Generate/update Swagger documentation
php artisan l5-swagger:generate
```

## 🚀 Quick Start

### Running Tests
```bash
# Navigate to backend directory
cd event-management-system/backend

# Install dependencies (if not already done)
composer install

# Run all tests
php artisan test

# Generate API documentation
php artisan l5-swagger:generate

# Start the server
php artisan serve

# Access API documentation
# Open http://localhost:8000/api/documentation
```

## 📊 Test Results

Latest test run results:
- **Total Tests**: 70
- **Passed**: 70
- **Failed**: 0
- **Assertions**: 398
- **Duration**: ~0.67s

### Test Categories
- **Unit Tests**: 36 tests (EventService: 16, AttendeeService: 20)
- **Feature Tests**: 32 tests (EventController: 14, AttendeeController: 18)
- **Example Tests**: 2 tests

## 🔧 Configuration

### Testing Configuration
- Database: SQLite (in-memory for tests)
- Test Environment: `.env.testing`
- PHPUnit Configuration: `phpunit.xml`

### Swagger Configuration
- Configuration File: `config/l5-swagger.php`
- Documentation Route: `/api/documentation`
- JSON Output: `storage/api-docs/api-docs.json`
- Annotations Path: `app/` directory

## 📝 Best Practices

### Testing
1. **Arrange-Act-Assert** pattern in all tests
2. **Database transactions** for test isolation
3. **Factory classes** for consistent test data
4. **Comprehensive edge case coverage**
5. **Meaningful test names** describing the scenario

### Documentation
1. **Complete endpoint documentation** with examples
2. **Detailed schema definitions** for all models
3. **Error response documentation** for all scenarios
4. **Request validation rules** clearly specified
5. **Response format consistency** across all endpoints

## 🛠️ Maintenance

### Adding New Tests
1. Create test file in appropriate directory (`tests/Unit/` or `tests/Feature/`)
2. Extend `TestCase` class
3. Use `RefreshDatabase` trait for database tests
4. Follow naming convention: `test_method_name_describes_scenario`

### Updating Documentation
1. Add Swagger annotations to new endpoints
2. Update schema definitions for model changes
3. Regenerate documentation: `php artisan l5-swagger:generate`
4. Test documentation in browser

This comprehensive testing and documentation setup ensures the Event Management System API is reliable, well-tested, and easy to use for developers.
