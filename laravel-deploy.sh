#!/bin/bash

# Laravel-Specific Deployment Script for Event Management System
echo "🚀 Laravel Event Management System Deployment"
echo "=============================================="

# Check if we're in the right directory
if [ ! -d "event-management-system/backend" ]; then
    echo "❌ Error: Please run this script from the project root directory"
    exit 1
fi

echo "📋 Laravel Deployment Process Started..."

cd event-management-system/backend

# 1. Environment Setup
echo "🔧 Setting up Laravel environment..."
if [ ! -f ".env" ]; then
    echo "📝 Creating production environment file..."
    cp .env.example .env
    
    # Generate application key
    php artisan key:generate
    
    echo "⚠️  Please update your .env file with production settings:"
    echo "   - Set APP_ENV=production"
    echo "   - Set APP_DEBUG=false"
    echo "   - Configure your database settings"
    echo "   - Set APP_URL to your production URL"
fi

# 2. Install Dependencies
echo "📦 Installing production dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction

# 3. Database Setup
echo "🗄️ Setting up database..."
if [ ! -f "database/database.sqlite" ]; then
    touch database/database.sqlite
fi

# Run migrations
php artisan migrate --force

# Ask for sample data
read -p "🌱 Do you want to seed with sample data for demo? (y/n): " seed_choice
if [ "$seed_choice" = "y" ]; then
    php artisan db:seed --class=SampleDataSeeder --force
fi

# 4. Laravel Optimization
echo "⚡ Optimizing Laravel for production..."

# Clear all caches first
php artisan optimize:clear

# Cache configurations
php artisan config:cache
echo "✅ Configuration cached"

# Cache routes
php artisan route:cache
echo "✅ Routes cached"

# Cache views
php artisan view:cache
echo "✅ Views cached"

# Optimize autoloader
composer dump-autoload --optimize
echo "✅ Autoloader optimized"

# Generate API documentation
php artisan l5-swagger:generate
echo "✅ API documentation generated"

# 5. File Permissions (for Linux/Unix servers)
echo "🔐 Setting file permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
echo "✅ Permissions set"

# 6. Create deployment archive (optional)
read -p "📦 Create deployment archive for upload? (y/n): " archive_choice
if [ "$archive_choice" = "y" ]; then
    cd ..
    echo "📦 Creating deployment archive..."
    tar -czf event-management-system-production.tar.gz \
        --exclude='backend/node_modules' \
        --exclude='backend/.git' \
        --exclude='backend/storage/logs/*' \
        --exclude='backend/tests' \
        backend/
    echo "✅ Archive created: event-management-system-production.tar.gz"
    cd backend
fi

echo ""
echo "🎉 Laravel deployment preparation complete!"
echo ""
echo "📋 Next Steps:"
echo "1. Upload files to your server"
echo "2. Point web server document root to 'public' directory"
echo "3. Update .env with production database and app settings"
echo "4. Run 'php artisan migrate --force' on production server"
echo "5. Set up proper file permissions (755 for directories, 644 for files)"
echo "6. Configure your web server (Apache/Nginx)"
echo ""
echo "🔗 Laravel Deployment Options:"
echo "• Laravel Forge: https://forge.laravel.com"
echo "• Laravel Vapor: https://vapor.laravel.com"
echo "• Laravel Envoyer: https://envoyer.io"
echo ""
echo "📚 Full deployment guide available in README.md"

# 7. Verify deployment readiness
echo ""
echo "🔍 Deployment Readiness Check:"
php artisan about --only=environment
echo ""

# Test API endpoint
echo "🧪 Testing API health endpoint..."
if command -v curl &> /dev/null; then
    # Start server briefly for testing
    php artisan serve &
    SERVER_PID=$!
    sleep 3
    
    echo "Testing: http://localhost:8000/api/health"
    curl -s http://localhost:8000/api/health | head -c 100
    echo ""
    
    # Stop test server
    kill $SERVER_PID 2>/dev/null
    echo "✅ API test completed"
else
    echo "ℹ️  Install curl to test API endpoints"
fi

echo ""
echo "🚀 Ready for Laravel deployment!"
