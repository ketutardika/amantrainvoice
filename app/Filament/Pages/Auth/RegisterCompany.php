<?php

namespace App\Filament\Pages\Auth;

use App\Models\Company;
use App\Models\InvoiceSettings;
use App\Models\Tax;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Support\Str;

class RegisterCompany extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register Company';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Company Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Company Email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Company Phone')
                    ->tel()
                    ->maxLength(255),
                Textarea::make('address')
                    ->label('Company Address')
                    ->rows(3),
            ]);
    }

    protected function handleRegistration(array $data): Company
    {
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);

        $company = Company::create($data);

        // Associate user with the new company
        auth()->user()->update(['company_id' => $company->id]);

        // Seed default invoice settings for this company
        $this->seedDefaultSettings($company);

        // Seed default taxes for this company
        $this->seedDefaultTaxes($company);

        return $company;
    }

    private function seedDefaultSettings(Company $company): void
    {
        $defaults = [
            ['key' => 'company_name', 'value' => $company->name, 'type' => 'text', 'description' => 'Company name displayed on invoices'],
            ['key' => 'company_email', 'value' => $company->email ?? '', 'type' => 'email', 'description' => 'Primary company email address'],
            ['key' => 'company_phone', 'value' => $company->phone ?? '', 'type' => 'text', 'description' => 'Company contact phone number'],
            ['key' => 'company_address', 'value' => $company->address ?? '', 'type' => 'textarea', 'description' => 'Complete company address for invoices'],
            ['key' => 'invoice_prefix', 'value' => 'INV', 'type' => 'text', 'description' => 'Prefix for invoice numbers'],
            ['key' => 'default_currency', 'value' => 'IDR', 'type' => 'text', 'description' => 'Default currency for new invoices'],
            ['key' => 'default_payment_terms', 'value' => '30', 'type' => 'number', 'description' => 'Default payment terms in days'],
        ];

        foreach ($defaults as $setting) {
            InvoiceSettings::create(array_merge($setting, ['company_id' => $company->id]));
        }
    }

    private function seedDefaultTaxes(Company $company): void
    {
        Tax::create([
            'company_id' => $company->id,
            'name' => 'PPN',
            'code' => 'PPN',
            'rate' => 11.00,
            'type' => 'percentage',
            'description' => 'Pajak Pertambahan Nilai (Value Added Tax)',
            'is_active' => true,
        ]);
    }
}
