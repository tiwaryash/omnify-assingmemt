-- Event Management System Database Schema
-- Generated: 2025-09-27
-- Database: SQLite

-- ============================================
-- Events Table
-- ============================================
CREATE TABLE IF NOT EXISTS "events" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "name" VARCHAR NOT NULL,
    "location" VARCHAR NOT NULL,
    "start_time" DATETIME NOT NULL,
    "end_time" DATETIME NOT NULL,
    "max_capacity" INTEGER NOT NULL,
    "current_attendees" INTEGER NOT NULL DEFAULT '0',
    "timezone" VARCHAR NOT NULL DEFAULT 'Asia/Kolkata',
    "created_at" DATETIME,
    "updated_at" DATETIME
);

-- Events Table Indexes
CREATE INDEX "events_start_time_index" ON "events" ("start_time");
CREATE INDEX "events_location_index" ON "events" ("location");

-- ============================================
-- Attendees Table
-- ============================================
CREATE TABLE IF NOT EXISTS "attendees" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "event_id" INTEGER NOT NULL,
    "name" VARCHAR NOT NULL,
    "email" VARCHAR NOT NULL,
    "created_at" DATETIME,
    "updated_at" DATETIME,
    FOREIGN KEY("event_id") REFERENCES "events"("id") ON DELETE CASCADE
);

-- Attendees Table Indexes
CREATE UNIQUE INDEX "attendees_event_id_email_unique" ON "attendees" ("event_id", "email");
CREATE INDEX "attendees_email_index" ON "attendees" ("email");
CREATE INDEX "attendees_event_id_index" ON "attendees" ("event_id");

-- ============================================
-- Sample Data (Optional)
-- ============================================

-- Sample Events
INSERT INTO events (name, location, start_time, end_time, max_capacity, timezone) VALUES
('Tech Conference 2025', 'Mumbai, India', '2025-12-01 04:30:00', '2025-12-01 12:30:00', 100, 'Asia/Kolkata'),
('Web Development Workshop', 'Delhi, India', '2025-12-15 03:00:00', '2025-12-15 08:00:00', 50, 'Asia/Kolkata'),
('AI/ML Symposium', 'Bangalore, India', '2025-12-20 04:00:00', '2025-12-20 11:00:00', 200, 'Asia/Kolkata');

-- Sample Attendees
INSERT INTO attendees (event_id, name, email) VALUES
(1, 'John Doe', 'john.doe@example.com'),
(1, 'Jane Smith', 'jane.smith@example.com'),
(2, 'Bob Wilson', 'bob.wilson@example.com'),
(3, 'Alice Johnson', 'alice.johnson@example.com');

-- ============================================
-- Constraints and Business Rules
-- ============================================

-- 1. max_capacity must be positive (enforced at application level)
-- 2. current_attendees must be non-negative (enforced at application level)
-- 3. current_attendees <= max_capacity (enforced at application level)
-- 4. start_time < end_time (enforced at application level)
-- 5. Unique email per event (enforced by database constraint)
-- 6. Referential integrity (enforced by foreign key constraint)

-- ============================================
-- Performance Notes
-- ============================================

-- Indexes created for:
-- - events.start_time: For filtering upcoming events
-- - events.location: For location-based searches
-- - attendees.event_id: For joining with events table
-- - attendees.email: For email-based lookups
-- - (attendees.event_id, attendees.email): For uniqueness and fast lookups

-- ============================================
-- Usage Examples
-- ============================================

-- Get all upcoming events:
-- SELECT * FROM events WHERE start_time > datetime('now') ORDER BY start_time ASC;

-- Get event with attendee count:
-- SELECT e.*, COUNT(a.id) as attendee_count 
-- FROM events e LEFT JOIN attendees a ON e.id = a.event_id 
-- WHERE e.id = ? GROUP BY e.id;

-- Check if email is registered for event:
-- SELECT EXISTS(SELECT 1 FROM attendees WHERE event_id = ? AND email = ?);

-- Get attendees for event with pagination:
-- SELECT * FROM attendees WHERE event_id = ? ORDER BY created_at ASC LIMIT ? OFFSET ?;
