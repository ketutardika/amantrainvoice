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
- **Key Dependencies**:
  - [DomPDF](https://github.com/barryvdh/laravel-dompdf) for PDF generation
  - [Laravel Excel](https://laravel-excel.com/) for report exports
  - [Spatie Permissions](https://spatie.be/docs/laravel-permission) for role-based access control
  - [Laravel Sanctum](https://laravel.com/docs/sanctum) for API authentication
- **Relationships**: Structured connections between Users, Clients, Invoices, Payments, and Projects.
- **Frontend Assets**: Vite-based asset compilation for modern frontend workflows.

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