# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 10 invoice management system built with PHP 8.1+ that provides comprehensive invoice lifecycle management, client handling, payment processing, and PDF generation. The system uses Filament 3.0 as the admin panel framework and includes multi-tenancy with role-based permissions.

## Key Commands

### Development Commands
- `php artisan serve` - Start the Laravel development server
- `npm run dev` - Start Vite development server for frontend assets
- `npm run build` - Build production assets with Vite

### Database Commands
- `php artisan migrate` - Run database migrations
- `php artisan migrate:fresh --seed` - Fresh migration with seeders
- `php artisan db:seed` - Run database seeders

### Testing & Quality
- `./vendor/bin/phpunit` - Run PHPUnit tests
- `php artisan test` - Run Laravel tests
- `./vendor/bin/pint` - Run Laravel Pint code formatter

### Cache & Optimization
- `php artisan config:cache` - Cache configuration
- `php artisan route:cache` - Cache routes
- `php artisan view:cache` - Cache views
- `php artisan optimize` - Optimize the application

## Architecture

### Core Models & Relationships
- **Invoice** (main model) → Client, Project, User, InvoiceItems, Payments, InvoiceStatusLogs
- **Client** → Invoices (has many)
- **Project** → Invoices (has many)
- **User** → Invoices (has many)
- **InvoiceItem** → Invoice (belongs to)
- **Payment** → Invoice (belongs to)

### Invoice Status Flow
`draft` → `sent` → `viewed` → `partial_paid`/`paid` → `overdue` (if unpaid past due date) → `cancelled`

### Database Schema Patterns
- All monetary fields use `decimal(15,2)` precision
- Soft deletes implemented on core models
- JSON columns for flexible data (`email_log`, `custom_fields`)
- Foreign key constraints with appropriate cascade/restrict rules
- Optimized indexes on frequently queried columns

### Key Features
- **PDF Generation**: Uses DomPDF (`barryvdh/laravel-dompdf`)
- **Excel Exports**: Uses Laravel Excel (`maatwebsite/excel`)
- **Permissions**: Spatie Laravel Permission package
- **API Authentication**: Laravel Sanctum
- **Admin Panel**: Filament 3.0
- **Multi-currency**: Support with exchange rates
- **Status Tracking**: Comprehensive invoice lifecycle logging

### File Structure Patterns
- Controllers are resource-based but currently contain placeholder methods
- Models use proper Eloquent relationships and casting
- Migrations follow Laravel naming conventions with proper foreign key constraints
- Configuration files are standard Laravel setup with additional packages (dompdf, excel, permission)

## Development Notes

### Testing Setup
- PHPUnit configured for both Unit and Feature tests
- Test environment uses array cache/session drivers
- Database testing can use SQLite in-memory or configured database

### Frontend Assets
- Vite-based build system
- Filament provides pre-built admin interface components
- Custom CSS/JS in `resources/` directory

### Package Dependencies
Key packages that define the architecture:
- `filament/filament: 3.0` - Admin panel framework
- `spatie/laravel-permission: ^6.21` - Role/permission management
- `barryvdh/laravel-dompdf: ^3.1` - PDF generation
- `maatwebsite/excel: ^3.1` - Excel import/export
- `laravel/sanctum: ^3.3` - API authentication