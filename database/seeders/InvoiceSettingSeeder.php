<?php

namespace Database\Seeders;

use App\Models\InvoiceSetting;
use Illuminate\Database\Seeder;

class InvoiceSettingSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            [
                'key' => 'company_name',
                'value' => 'Amantra Invoice System',
                'type' => 'text',
                'description' => 'Company name for invoices'
            ],
            [
                'key' => 'company_address',
                'value' => 'Jl. Sudirman No. 123, Jakarta Pusat, DKI Jakarta 10110',
                'type' => 'text',
                'description' => 'Company address for invoices'
            ],
            [
                'key' => 'company_phone',
                'value' => '+62 21 1234 5678',
                'type' => 'text',
                'description' => 'Company phone number'
            ],
            [
                'key' => 'company_email',
                'value' => 'info@amantrainvoice.com',
                'type' => 'text',
                'description' => 'Company email address'
            ],
            [
                'key' => 'company_website',
                'value' => 'https://amantrainvoice.com',
                'type' => 'text',
                'description' => 'Company website URL'
            ],
            [
                'key' => 'company_logo',
                'value' => '',
                'type' => 'text',
                'description' => 'Company logo path'
            ],
            [
                'key' => 'default_payment_terms',
                'value' => '14',
                'type' => 'number',
                'description' => 'Default payment terms in days'
            ],
            [
                'key' => 'default_currency',
                'value' => 'IDR',
                'type' => 'text',
                'description' => 'Default currency code'
            ],
            [
                'key' => 'auto_send_invoice',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Auto send invoice via email'
            ],
            [
                'key' => 'invoice_prefix',
                'value' => 'INV',
                'type' => 'text',
                'description' => 'Invoice number prefix'
            ],
            [
                'key' => 'late_fee_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Enable late fees for overdue invoices'
            ],
            [
                'key' => 'late_fee_percentage',
                'value' => '2',
                'type' => 'number',
                'description' => 'Late fee percentage per month'
            ],
            [
                'key' => 'default_terms_conditions',
                'value' => 'Payment is due within specified days. Late payments may incur additional charges.',
                'type' => 'text',
                'description' => 'Default terms and conditions'
            ],
            [
                'key' => 'default_notes',
                'value' => 'Thank you for your business!',
                'type' => 'text',
                'description' => 'Default invoice notes'
            ],
            [
                'key' => 'tax_number',
                'value' => '01.234.567.8-901.000',
                'type' => 'text',
                'description' => 'Company tax number (NPWP)'
            ]
        ];

        foreach ($settings as $setting) {
            InvoiceSetting::create($setting);
        }
    }
}