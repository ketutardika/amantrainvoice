# InstantInvoice

A multi-tenant SaaS invoice management system built with **Laravel 10** and **Filament 3.0**. Each user registers, creates a company, and manages their own clients, invoices, payments, and projects — fully isolated from other tenants. Default currency is IDR (Indonesian Rupiah).

---

## Features

- **Invoice Lifecycle**: Draft → Sent → Viewed → Partially Paid → Paid → Overdue → Cancelled
- **Client Management**: Full profiles with type (individual/company), address, tax number, credit limit, and payment terms
- **Project Management**: Budget tracking, progress percentage, status flow, and invoice association
- **Payment Tracking**: Multiple payment methods, verification workflow, and receipt attachments
- **PDF Generation**: Professional invoice PDFs via DomPDF with QR code for quick client access
- **Public Invoice Links**: Signed, unauthenticated URLs so clients can view invoices without logging in
- **Export (CSV & XLSX)**: One-click export for Clients, Invoices, Projects, and Payments with raw numeric values
- **Multi-Tenancy**: Company-based isolation — each tenant has their own data, slug-based URL, and settings
- **Invoice Settings**: Configurable invoice prefix, default notes/terms, and branding per company
- **Tax Settings**: Per-company tax rates (percentage, fixed, or compound)

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 10 (PHP 8.1+) |
| Admin Panel | Filament 3.0 (TALL stack) |
| PDF Generation | barryvdh/laravel-dompdf |
| Excel / CSV Export | maatwebsite/excel |
| QR Code | simplesoftwareio/simple-qrcode |
| Permissions | spatie/laravel-permission |
| API Auth | Laravel Sanctum |
| Frontend | Vite + Tailwind CSS |

---

## Multi-Tenancy

Uses Filament's built-in tenant support scoped to the `Company` model. Admin panel URLs follow the pattern:

```
/admin/{company-slug}/resources/...
```

All resource queries are automatically scoped to the authenticated user's company.

---

## Installation

### Prerequisites
- PHP 8.1+
- Composer
- Node.js & npm
- MySQL or compatible database

### Steps

```bash
# 1. Clone the repository
git clone https://github.com/ketutardika/amantrainvoice.git
cd amantrainvoice

# 2. Install dependencies
composer install
npm install

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Set up the database in .env, then run migrations and seed
php artisan migrate:fresh --seed

# 5. Start development servers
php artisan serve
npm run dev
```

### Seeded Credentials
- **URL**: `http://localhost:8000/admin/demo-company`
- **Email**: `admin@amantrainvoice.com`
- **Password**: `password`

---

## Key Commands

```bash
php artisan serve                          # Start Laravel dev server
npm run dev                                # Start Vite (required for Filament assets)
php artisan migrate                        # Run pending migrations
php artisan migrate:fresh --seed           # Reset DB with demo data
php artisan test                           # Run all tests
./vendor/bin/pint                          # Laravel Pint code formatter (PSR-12)
```

---

## Export

Each resource table (Clients, Invoices, Projects, Payments) has an **Export** dropdown button in the table header with two options:

- **Export CSV** — plain text, raw numeric values (no thousand separators)
- **Export XLSX** — Excel workbook, bold headers, auto-sized columns

Exported filenames follow the pattern:
```
{app-host}-export-{model}-{company-slug}-{YYYY-MM-DD_HHmmss}.{ext}
```

---

## PDF

Invoice PDFs are generated via DomPDF and include a QR code linking to the public invoice URL.

### Routes
| Route | Description |
|---|---|
| `GET /invoices/{invoice}/pdf` | Streams PDF (authenticated) |
| `GET /invoices/{invoice}/view` | Inline PDF view (authenticated) |
| `GET /invoices/{tenant}/{uuid}/pdf?signature=...` | Public signed URL for clients |

### PDF filename pattern
```
{app-host}-export-invoice-{company-slug}-{invoice-number}={YYYY-MM-DD_HHmmss}.pdf
```

### Browser tab title
```
{Company Name} - Invoice {number} - {app-host}
```

All filenames and titles derive the hostname from `APP_URL` in `.env` — no hardcoded domain.

---

## Project Structure

```
app/
├── Exports/                  # CSV/XLSX export classes
│   ├── ClientsExport.php
│   ├── InvoicesExport.php
│   ├── ProjectsExport.php
│   └── PaymentsExport.php
├── Filament/
│   ├── Pages/                # Dashboard, InvoiceSettings, TaxSettings, Auth
│   └── Resources/            # ClientResource, InvoiceResource, ProjectResource, PaymentResource
├── Http/Controllers/
│   ├── ExportController.php
│   ├── InvoiceController.php
│   ├── PublicInvoiceController.php
│   └── ViewInvoice.php
└── Models/
    ├── Company.php
    ├── Client.php
    ├── Invoice.php
    ├── InvoiceItem.php
    ├── Payment.php
    ├── Project.php
    ├── Tax.php
    └── InvoiceSettings.php
```

---

## License

MIT
