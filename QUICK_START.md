# Quick Start Guide

## One-Command Setup

### Prerequisites Check
```bash
php --version    # Should be >= 8.2
composer --version
node --version   # Should be >= 18.0
npm --version
```

### Backend Setup (Laravel API)
```bash
cd event-management-system/backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan l5-swagger:generate
php artisan serve
```
**Backend runs on**: http://localhost:8000

### Frontend Setup (Next.js)
```bash
cd event-management-system/frontend
npm install
npm run dev
```
**Frontend runs on**: http://localhost:3000

## Verify Installation

### 1. Test API Health
```bash
curl http://localhost:8000/api/health
```

### 2. View API Documentation
Open: http://localhost:8000/api/documentation

### 3. Run Tests
```bash
cd backend && php artisan test
```

### 4. Access Frontend
Open: http://localhost:3000

## Key URLs
- **Frontend**: http://localhost:3000
- **API Base**: http://localhost:8000/api
- **API Docs**: http://localhost:8000/api/documentation
- **Health Check**: http://localhost:8000/api/health

## Test the System

### Create an Event
```bash
curl -X POST "http://localhost:8000/api/events" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Event",
    "location": "Mumbai, India",
    "start_time": "2025-12-01 10:00:00",
    "end_time": "2025-12-01 18:00:00",
    "max_capacity": 100,
    "timezone": "Asia/Kolkata"
  }'
```

### Register Attendee
```bash
curl -X POST "http://localhost:8000/api/events/1/register" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com"
  }'
```

### List Events
```bash
curl "http://localhost:8000/api/events"
```

##  Troubleshooting

### Common Issues
1. **Port conflicts**: Change ports in `.env` or use different terminals
2. **Database permissions**: Ensure `database/` directory is writable
3. **Composer issues**: Run `composer install --no-dev` for production

### Reset Database
```bash
cd backend
php artisan migrate:fresh
```

### Regenerate Documentation
```bash
php artisan l5-swagger:generate
```

## Success Checklist
- [ ] Backend server running on port 8000
- [ ] Frontend server running on port 3000
- [ ] API health check returns success
- [ ] Swagger documentation loads
- [ ] All tests pass (70 tests)
- [ ] Can create events via API
- [ ] Can register attendees via API

**You're ready to go!**
