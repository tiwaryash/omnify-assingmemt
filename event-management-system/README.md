# Mini Event Management System

A full-stack web application for managing events and attendee registrations, built with Laravel (Backend) and Next.js (Frontend).

## 🚀 Features

### Core Features
- **Event Management**: Create, view, and manage events with details like name, location, date/time, and capacity
- **Attendee Registration**: Users can register for events with name and email validation
- **Capacity Management**: Automatic tracking of available spots and full capacity handling
- **Real-time Updates**: Dynamic UI updates for event capacity and registration status
- **Timezone Support**: Events display with proper timezone information (default: IST)

### Technical Features
- **RESTful API**: Clean and well-documented API endpoints
- **Form Validation**: Client-side and server-side validation with proper error handling
- **Responsive Design**: Mobile-friendly interface using Tailwind CSS
- **Error Handling**: Comprehensive error handling with user-friendly messages
- **Database Relations**: Proper foreign key relationships and constraints
- **Search & Filter**: Find events by name/location and filter by availability

## 🏗️ Architecture

### Backend (Laravel)
- **Models**: Eloquent models with relationships and business logic
- **Services**: Service layer for complex business logic
- **Controllers**: API controllers with validation and error handling
- **Database**: SQLite database with proper migrations and relationships
- **API Routes**: RESTful endpoints for events and attendees

### Frontend (Next.js)
- **Components**: Reusable React components for events, forms, and UI elements
- **API Integration**: Axios-based API client with interceptors
- **Forms**: React Hook Form with Zod validation
- **Styling**: Tailwind CSS with custom components
- **State Management**: React state with proper error handling

## 📋 API Endpoints

### Events
- `GET /api/events` - Get all upcoming events
- `POST /api/events` - Create a new event
- `GET /api/events/{id}` - Get a specific event
- `PUT /api/events/{id}` - Update an event
- `DELETE /api/events/{id}` - Delete an event

### Attendees
- `POST /api/events/{eventId}/register` - Register for an event
- `GET /api/events/{eventId}/attendees` - Get event attendees
- `DELETE /api/events/{eventId}/attendees/{attendeeId}` - Unregister attendee

## 🛠️ Technology Stack

### Backend
- **PHP 8.1+**
- **Laravel 11**
- **SQLite Database**
- **Composer** for dependency management

### Frontend
- **Node.js 18+**
- **Next.js 14** (App Router)
- **React 18**
- **TypeScript**
- **Tailwind CSS**
- **React Hook Form**
- **Zod** for validation
- **Axios** for API calls
- **Lucide React** for icons

## 🚀 Getting Started

### Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js 18 or higher
- npm or yarn

### Backend Setup

1. **Navigate to the backend directory:**
   ```bash
   cd event-management-system/backend
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Set up the database:**
   ```bash
   php artisan migrate
   ```

4. **Start the Laravel development server:**
   ```bash
   php artisan serve --port=8000
   ```

   The backend API will be available at `http://localhost:8000`

### Frontend Setup

1. **Navigate to the frontend directory:**
   ```bash
   cd event-management-system/frontend
   ```

2. **Install Node.js dependencies:**
   ```bash
   npm install
   ```

3. **Start the Next.js development server:**
   ```bash
   npm run dev
   ```

   The frontend will be available at `http://localhost:3000`

## 🧪 Testing the Application

### Manual Testing

1. **Access the application** at `http://localhost:3000`

2. **Create an Event:**
   - Click "Create Event" button
   - Fill in event details (name, location, date/time, capacity)
   - Submit the form

3. **Register for an Event:**
   - Click "Register" on any event card
   - Fill in your name and email
   - Submit the registration form

4. **View Events:**
   - Browse all events on the main page
   - Use search to find specific events
   - Filter by availability status

### API Testing

You can test the API endpoints directly using tools like Postman or curl:

```bash
# Get all events
curl http://localhost:8000/api/events

# Create an event
curl -X POST http://localhost:8000/api/events \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Tech Conference 2025",
    "location": "Mumbai, India",
    "start_time": "2025-12-15T09:00:00",
    "end_time": "2025-12-15T17:00:00",
    "max_capacity": 100
  }'

# Register for an event
curl -X POST http://localhost:8000/api/events/1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com"
  }'
```

## 🗂️ Project Structure

```
event-management-system/
├── backend/                 # Laravel Backend
│   ├── app/
│   │   ├── Http/Controllers/Api/    # API Controllers
│   │   ├── Models/                  # Eloquent Models
│   │   └── Services/                # Business Logic Services
│   ├── database/
│   │   └── migrations/              # Database Migrations
│   ├── routes/
│   │   └── api.php                  # API Routes
│   └── database.sqlite              # SQLite Database
├── frontend/               # Next.js Frontend
│   ├── src/
│   │   ├── app/                     # Next.js App Router
│   │   ├── components/              # React Components
│   │   │   ├── ui/                  # Base UI Components
│   │   │   ├── EventCard.tsx
│   │   │   ├── EventForm.tsx
│   │   │   ├── AttendeeForm.tsx
│   │   │   └── EventsList.tsx
│   │   └── lib/
│   │       ├── api.ts               # API Client
│   │       └── utils.ts             # Utility Functions
│   └── package.json
└── README.md
```

## 🔧 Configuration

### Backend Configuration
- Database: SQLite (configured in `config/database.php`)
- CORS: Enabled for frontend requests
- API Routes: Prefixed with `/api`

### Frontend Configuration
- API URL: `http://localhost:8000/api` (configured in `next.config.js`)
- Styling: Tailwind CSS with custom utilities
- Forms: React Hook Form with Zod validation

## ✨ Key Features Implemented

### Backend Best Practices
- **Service Layer Architecture**: Business logic separated from controllers
- **Eloquent Relationships**: Proper model relationships and constraints
- **Input Validation**: Request validation with custom error messages
- **Error Handling**: Consistent API error responses
- **Database Transactions**: Atomic operations for data integrity
- **Query Optimization**: Efficient database queries with proper indexing

### Frontend Best Practices
- **Component Architecture**: Reusable and composable React components
- **Type Safety**: Full TypeScript implementation
- **Form Validation**: Client-side validation with server-side backup
- **Error Handling**: User-friendly error messages and loading states
- **Responsive Design**: Mobile-first responsive layout
- **Performance**: Optimized rendering and API calls

### Security Features
- **Input Sanitization**: Protection against SQL injection and XSS
- **CORS Configuration**: Proper cross-origin request handling
- **Email Validation**: Preventing duplicate registrations per event
- **Capacity Validation**: Server-side capacity checking with race condition protection

## 🚀 Production Deployment

### Backend Deployment
1. Set up a production database (MySQL/PostgreSQL)
2. Configure environment variables in `.env`
3. Run migrations: `php artisan migrate --force`
4. Set up a web server (Apache/Nginx) with PHP-FPM
5. Configure SSL certificates

### Frontend Deployment
1. Build the application: `npm run build`
2. Deploy to a static hosting service (Vercel, Netlify)
3. Update API URL for production environment
4. Configure domain and SSL

## 📝 Future Enhancements

- **Authentication System**: User login and event ownership
- **Email Notifications**: Automated email confirmations
- **Event Categories**: Organize events by categories
- **Advanced Search**: Location-based and date range filtering
- **Payment Integration**: Paid event registration
- **Admin Dashboard**: Advanced event management interface
- **Analytics**: Event attendance and registration analytics
- **API Documentation**: Swagger/OpenAPI documentation

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## 📄 License

This project is created for demonstration purposes and is available under the MIT License.

---

**Built with ❤️ using Laravel and Next.js**
