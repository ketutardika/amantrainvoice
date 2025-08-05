<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InvoiceSetting;

class InvoiceSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $settings = [
            [
                'key' => 'company_name',
                'value' => 'Your Company Name',
                'type' => 'text',
                'description' => 'Company name for invoices'
            ],
            [
                'key' => 'company_address',
                'value' => 'Your Company Address',
                'type' => 'text',
                'description' => 'Company address for invoices'
            ],
            [
                'key' => 'company_phone',
                'value' => '+62xxx-xxxx-xxxx',
                'type' => 'text',
                'description' => 'Company phone number'
            ],
            [
                'key' => 'company_email',
                'value' => 'info@company.com',
                'type' => 'text',
                'description' => 'Company email address'
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
            ]
        ];

        foreach ($settings as $setting) {
            InvoiceSetting::create($setting);
        }
    }
}
