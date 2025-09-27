# 🗄️ Database Schema Documentation

## Overview
The Event Management System uses **SQLite** as the database with a clean, normalized schema designed for scalability and data integrity.

## 📊 Entity Relationship Diagram

```
┌─────────────────┐         ┌─────────────────┐
│     Events      │         │   Attendees     │
├─────────────────┤         ├─────────────────┤
│ id (PK)         │◄────────┤ id (PK)         │
│ name            │    1:N  │ event_id (FK)   │
│ location        │         │ name            │
│ start_time      │         │ email           │
│ end_time        │         │ created_at      │
│ max_capacity    │         │ updated_at      │
│ current_attendees│         └─────────────────┘
│ timezone        │
│ created_at      │
│ updated_at      │
└─────────────────┘
```

## 📋 Table Definitions

### `events` Table

**Purpose**: Stores event information including scheduling, location, and capacity details.

```sql
CREATE TABLE events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    max_capacity INTEGER NOT NULL CHECK (max_capacity > 0),
    current_attendees INTEGER NOT NULL DEFAULT 0 CHECK (current_attendees >= 0),
    timezone VARCHAR(255) NOT NULL DEFAULT 'Asia/Kolkata',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

#### Field Details

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| `id` | INTEGER | PRIMARY KEY, AUTO_INCREMENT | Unique event identifier |
| `name` | VARCHAR(255) | NOT NULL | Event name/title |
| `location` | VARCHAR(255) | NOT NULL | Event venue/location |
| `start_time` | DATETIME | NOT NULL | Event start time (UTC) |
| `end_time` | DATETIME | NOT NULL | Event end time (UTC) |
| `max_capacity` | INTEGER | NOT NULL, > 0 | Maximum attendee capacity |
| `current_attendees` | INTEGER | NOT NULL, >= 0, DEFAULT 0 | Current registered attendees |
| `timezone` | VARCHAR(255) | NOT NULL, DEFAULT 'Asia/Kolkata' | Event timezone |
| `created_at` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Record last update time |

#### Indexes
```sql
CREATE INDEX idx_events_start_time ON events(start_time);
CREATE INDEX idx_events_location ON events(location);
```

### `attendees` Table

**Purpose**: Stores attendee registration information with relationship to events.

```sql
CREATE TABLE attendees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_id INTEGER NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE(event_id, email)
);
```

#### Field Details

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| `id` | INTEGER | PRIMARY KEY, AUTO_INCREMENT | Unique attendee identifier |
| `event_id` | INTEGER | NOT NULL, FOREIGN KEY | Reference to events table |
| `name` | VARCHAR(255) | NOT NULL | Attendee full name |
| `email` | VARCHAR(255) | NOT NULL | Attendee email address |
| `created_at` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Registration time |
| `updated_at` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Record last update time |

#### Constraints
```sql
-- Prevent duplicate email registrations per event
CONSTRAINT unique_email_per_event UNIQUE (event_id, email)

-- Maintain referential integrity
CONSTRAINT fk_attendees_event_id 
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
```

#### Indexes
```sql
CREATE INDEX idx_attendees_event_id ON attendees(event_id);
CREATE INDEX idx_attendees_email ON attendees(email);
CREATE UNIQUE INDEX idx_attendees_event_email ON attendees(event_id, email);
```

## 🔗 Relationships

### One-to-Many (Events → Attendees)
- **Parent**: `events` table
- **Child**: `attendees` table
- **Foreign Key**: `attendees.event_id` → `events.id`
- **Cascade**: `ON DELETE CASCADE` (removing event removes all attendees)

### Business Rules Enforced by Schema

1. **Capacity Constraint**: 
   - `max_capacity` must be positive
   - `current_attendees` cannot be negative
   - Application logic ensures `current_attendees <= max_capacity`

2. **Email Uniqueness**: 
   - Unique constraint on `(event_id, email)`
   - Same email can register for different events
   - Prevents duplicate registrations per event

3. **Temporal Integrity**:
   - `end_time` should be after `start_time` (enforced at application level)
   - Times stored as UTC for consistency

4. **Data Integrity**:
   - Foreign key constraints maintain referential integrity
   - Cascade delete ensures orphaned records are cleaned up

## 📝 Migration Files

### Event Migration (`2025_09_27_094014_create_events_table.php`)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('max_capacity');
            $table->integer('current_attendees')->default(0);
            $table->string('timezone')->default('Asia/Kolkata');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('start_time');
            $table->index('location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
```

### Attendee Migration (`2025_09_27_094021_create_attendees_table.php`)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->timestamps();
            
            // Unique constraint: one email per event
            $table->unique(['event_id', 'email']);
            
            // Indexes for performance
            $table->index('event_id');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendees');
    }
};
```

## 🔍 Sample Queries

### Common Queries with Performance Considerations

#### 1. Get Upcoming Events
```sql
SELECT * FROM events 
WHERE start_time > datetime('now') 
ORDER BY start_time ASC
LIMIT 10 OFFSET 0;
```

#### 2. Get Event with Attendee Count
```sql
SELECT 
    e.*,
    COUNT(a.id) as attendee_count,
    (e.max_capacity - COUNT(a.id)) as remaining_capacity
FROM events e
LEFT JOIN attendees a ON e.id = a.event_id
WHERE e.id = ?
GROUP BY e.id;
```

#### 3. Check Registration Status
```sql
SELECT EXISTS(
    SELECT 1 FROM attendees 
    WHERE event_id = ? AND email = ?
) as is_registered;
```

#### 4. Get Event Attendees with Pagination
```sql
SELECT * FROM attendees 
WHERE event_id = ? 
ORDER BY created_at ASC
LIMIT 10 OFFSET 0;
```

#### 5. Search Attendees
```sql
SELECT * FROM attendees 
WHERE event_id = ? 
  AND (name LIKE '%search%' OR email LIKE '%search%')
ORDER BY created_at ASC;
```

## 📈 Performance Considerations

### Indexing Strategy
1. **Primary Keys**: Automatic unique indexes
2. **Foreign Keys**: Indexed for join performance
3. **Search Fields**: Indexed on frequently queried columns
4. **Composite Index**: `(event_id, email)` for uniqueness and lookups

### Query Optimization
1. **Pagination**: Using LIMIT/OFFSET for large result sets
2. **Joins**: Efficient LEFT JOIN for event-attendee relationships
3. **Filtering**: WHERE clauses on indexed columns
4. **Sorting**: ORDER BY on indexed columns when possible

### Scalability Notes
1. **Connection Pooling**: For high-concurrency scenarios
2. **Read Replicas**: For read-heavy workloads
3. **Caching**: Application-level caching for frequently accessed data
4. **Archiving**: Strategy for old events and attendees

## 🛡️ Data Integrity & Constraints

### Application-Level Validations
```php
// Event validation rules
'name' => 'required|string|max:255',
'location' => 'required|string|max:255',
'start_time' => 'required|date|after:now',
'end_time' => 'required|date|after:start_time',
'max_capacity' => 'required|integer|min:1',
'timezone' => 'nullable|string|in:' . implode(',', timezone_identifiers_list())

// Attendee validation rules
'name' => 'required|string|max:255',
'email' => 'required|email|max:255'
```

### Database-Level Constraints
```sql
-- Check constraints
CHECK (max_capacity > 0)
CHECK (current_attendees >= 0)

-- Unique constraints
UNIQUE (event_id, email)

-- Foreign key constraints
FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
```

## 🔄 Database Maintenance

### Regular Maintenance Tasks
1. **VACUUM**: Reclaim space from deleted records
2. **ANALYZE**: Update query planner statistics
3. **REINDEX**: Rebuild indexes for optimal performance

### Backup Strategy
```bash
# SQLite backup
sqlite3 database/database.sqlite ".backup backup_$(date +%Y%m%d).sqlite"

# Restore
sqlite3 database/database.sqlite ".restore backup_20250927.sqlite"
```

### Migration Commands
```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Reset and re-run all migrations
php artisan migrate:fresh

# Check migration status
php artisan migrate:status
```

## 📊 Sample Data

### Events Table Sample
```sql
INSERT INTO events (name, location, start_time, end_time, max_capacity, timezone) VALUES
('Tech Conference 2025', 'Mumbai, India', '2025-12-01 04:30:00', '2025-12-01 12:30:00', 100, 'Asia/Kolkata'),
('Web Development Workshop', 'Delhi, India', '2025-12-15 03:00:00', '2025-12-15 08:00:00', 50, 'Asia/Kolkata'),
('AI/ML Symposium', 'Bangalore, India', '2025-12-20 04:00:00', '2025-12-20 11:00:00', 200, 'Asia/Kolkata');
```

### Attendees Table Sample
```sql
INSERT INTO attendees (event_id, name, email) VALUES
(1, 'John Doe', 'john.doe@example.com'),
(1, 'Jane Smith', 'jane.smith@example.com'),
(2, 'Bob Wilson', 'bob.wilson@example.com'),
(3, 'Alice Johnson', 'alice.johnson@example.com');
```

---

This schema is designed for **scalability**, **performance**, and **data integrity** while maintaining simplicity and following database design best practices.
