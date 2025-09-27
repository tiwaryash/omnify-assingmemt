#!/bin/bash

# Event Management System Deployment Script
echo "🚀 Event Management System Deployment"
echo "========================================"

# Check if we're in the right directory
if [ ! -d "event-management-system" ]; then
    echo "❌ Error: Please run this script from the project root directory"
    exit 1
fi

echo "📋 Pre-deployment checklist:"
echo "1. Backend Setup"

cd event-management-system/backend

# Install dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Generate app key if not exists
if [ ! -f ".env" ]; then
    echo "📝 Creating environment file..."
    cp .env.example .env
    php artisan key:generate
fi

# Database setup
echo "🗄️ Setting up database..."
touch database/database.sqlite
php artisan migrate --force

# Optional: Seed with sample data
read -p "🌱 Do you want to seed with sample data? (y/n): " seed_choice
if [ "$seed_choice" = "y" ]; then
    php artisan db:seed --class=SampleDataSeeder --force
fi

# Generate API documentation
echo "📚 Generating API documentation..."
php artisan l5-swagger:generate

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Backend setup complete!"

# Frontend setup
echo ""
echo "2. Frontend Setup"
cd ../frontend

echo "📦 Installing Node.js dependencies..."
npm install

echo "🏗️ Building frontend..."
npm run build

echo "✅ Frontend setup complete!"

cd ../..

echo ""
echo "🎉 Deployment preparation complete!"
echo ""
echo "📋 Next steps for deployment:"
echo "1. Push to GitHub: git push origin main"
echo "2. Deploy backend to Railway/Heroku"
echo "3. Deploy frontend to Vercel/Netlify"
echo ""
echo "📚 View full deployment guide in README.md"
echo ""
echo "🔗 Your repository: https://github.com/tiwaryash/omnify-assingmemt.git"
