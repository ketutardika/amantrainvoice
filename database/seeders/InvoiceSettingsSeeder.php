<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\InvoiceSettings;

class InvoiceSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $company = Company::first();
        $companyId = $company?->id;

        $settings = [
            ['key' => 'company_name', 'value' => $company?->name ?? 'Your Company Name', 'type' => 'text', 'description' => 'Company name displayed on invoices'],
            ['key' => 'company_email', 'value' => $company?->email ?? 'info@company.com', 'type' => 'email', 'description' => 'Primary company email address'],
            ['key' => 'company_phone', 'value' => $company?->phone ?? '+62xxx-xxxx-xxxx', 'type' => 'text', 'description' => 'Company contact phone number'],
            ['key' => 'company_website', 'value' => 'https://yourcompany.com', 'type' => 'url', 'description' => 'Company website URL'],
            ['key' => 'company_address', 'value' => $company?->address ?? 'Your Company Address', 'type' => 'textarea', 'description' => 'Complete company address for invoices'],
            ['key' => 'invoice_prefix', 'value' => 'INV', 'type' => 'text', 'description' => 'Prefix for invoice numbers'],
            ['key' => 'default_currency', 'value' => 'IDR', 'type' => 'text', 'description' => 'Default currency for new invoices'],
            ['key' => 'default_payment_terms', 'value' => '30', 'type' => 'number', 'description' => 'Default payment terms in days'],
            ['key' => 'default_tax_rate', 'value' => '11.00', 'type' => 'number', 'description' => 'Default tax rate percentage'],
            ['key' => 'late_fee_percentage', 'value' => '2.00', 'type' => 'number', 'description' => 'Late fee percentage for overdue invoices'],
            ['key' => 'invoice_template', 'value' => 'modern', 'type' => 'text', 'description' => 'Default invoice template design'],
            ['key' => 'date_format', 'value' => 'd/m/Y', 'type' => 'text', 'description' => 'Date format used in invoices'],
            ['key' => 'invoice_footer_text', 'value' => 'Thank you for your business!', 'type' => 'textarea', 'description' => 'Text displayed in invoice footer'],
            ['key' => 'auto_send_invoice', 'value' => 'false', 'type' => 'boolean', 'description' => 'Automatically send invoices via email when created'],
            ['key' => 'send_payment_reminders', 'value' => 'true', 'type' => 'boolean', 'description' => 'Send automatic payment reminder emails'],
            ['key' => 'auto_follow_up', 'value' => 'true', 'type' => 'boolean', 'description' => 'Send follow-up emails for overdue invoices'],
            ['key' => 'reminder_days_before', 'value' => '3', 'type' => 'number', 'description' => 'Days before due date to send reminder'],
            ['key' => 'followup_days_after', 'value' => '7', 'type' => 'number', 'description' => 'Days after due date to send follow-up'],
            ['key' => 'default_terms_conditions', 'value' => 'Payment is due within the specified payment terms. Late payments may incur additional fees.', 'type' => 'textarea', 'description' => 'Default terms and conditions text'],
            ['key' => 'default_notes', 'value' => 'Please contact us if you have any questions about this invoice.', 'type' => 'textarea', 'description' => 'Default notes included in invoices'],
        ];

        foreach ($settings as $setting) {
            InvoiceSettings::updateOrCreate(
                ['key' => $setting['key'], 'company_id' => $companyId],
                array_merge($setting, ['company_id' => $companyId])
            );
        }
    }
}
