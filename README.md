# Invoice System

A full-featured invoice management system built with Laravel 10 and PHP 8.1+. It provides robust functionality for managing clients, processing payments, generating PDFs, and creating detailed reports. The system supports multi-tenancy, role-based permissions, and advanced features like recurring invoices and webhook integrations.

## Features
- **Invoice Lifecycle Management**: Create, send, track, and manage invoices (Draft → Sent → Viewed → Paid → Overdue).
- **Client Management**: Comprehensive client profiles with relationship handling.
- **Payment Processing**: Secure payment integration for seamless transactions.
- **PDF Generation**: Generate professional invoice PDFs using DomPDF.
- **Reporting**: Exportable reports with Laravel Excel for data analysis.
- **Multi-Tenancy**: Isolated tenant environments with role-based permissions via Spatie Permissions.
- **Advanced Features**:
  - Recurring invoices
  - Bulk operations
  - Webhook integrations
  - Public invoice links
- **API Support**: Secure API endpoints with Laravel Sanctum for authentication.

## Architecture
- **Framework**: Laravel 10 with PHP 8.1+
- **Admin Panel**: Filament 3.0 - Modern TALL stack admin panel
- **Key Dependencies**:
  - [Filament](https://filamentphp.com/) v3.0 for admin panel and resource management
  - [DomPDF](https://github.com/barryvdh/laravel-dompdf) for PDF generation
  - [Laravel Excel](https://laravel-excel.com/) for report exports
  - [Spatie Permissions](https://spatie.be/docs/laravel-permission) for role-based access control
  - [Laravel Sanctum](https://laravel.com/docs/sanctum) for API authentication
- **Relationships**: Structured connections between Users, Clients, Invoices, Payments, and Projects.
- **Frontend Assets**: Vite-based asset compilation for modern frontend workflows.

## Filament Admin Panel

This project uses **Filament 3.0**, a powerful admin panel framework built on the TALL stack (Tailwind, Alpine.js, Laravel, Livewire).

### Features Implemented with Filament
- **Resource Management**: Full CRUD operations for Invoices, Clients, Projects, and Users
- **Dashboard**: Real-time analytics and business insights
- **Role-Based Access Control**: Integration with Spatie Permissions for granular access management
- **Custom Actions**: Bulk operations, invoice status updates, and PDF generation
- **Relations Manager**: Seamless management of invoice items, payments, and status logs
- **Advanced Tables**: Filtering, sorting, searching, and bulk actions on all resources
- **Form Builder**: Dynamic forms with validation for data entry
- **Notifications**: Real-time user notifications and alerts

### Accessing the Admin Panel
After installation, access the Filament admin panel at:
```
http://your-domain.com/admin
```

Default admin credentials (if seeded):
- Email: admin@example.com
- Password: password

### Key Filament Resources
- **InvoiceResource**: Manage invoice lifecycle, items, and payments
- **ClientResource**: Client profile management and relationship tracking
- **ProjectResource**: Project creation and invoice association
- **UserResource**: User management with role assignment
- **PaymentResource**: Payment tracking and reconciliation

### Customization
Filament resources are located in `app/Filament/Resources/`. Each resource includes:
- Resource class for configuration
- Pages for List, Create, Edit, and View
- Relation managers for nested resources
- Custom actions and filters

## Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js and npm
- MySQL/PostgreSQL or compatible database
- Laravel-compatible server environment (e.g., Apache/Nginx)

## Installation
1. **Clone the Repository**:
   ```bash
   git clone https://github.com/ketutardika/amantrainvoice.git
   cd amantrainvoice