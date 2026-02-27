#!/usr/bin/env bash
# Render.com build script for Laravel

set -o errexit

echo "🔨 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "📦 Installing Node.js dependencies..."
npm ci

echo "🎨 Building frontend assets..."
npm run build

echo "🧹 Cleaning up..."
rm -rf node_modules

echo "✅ Build complete!"
