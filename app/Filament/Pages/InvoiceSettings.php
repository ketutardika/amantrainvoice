<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use App\Models\InvoiceSettings as InvoiceSettingsModel;

class InvoiceSettings extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.invoice-settings';
    protected static ?string $title = 'Invoice Settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getSettingsData());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Company Information')
                    ->description('Basic company details that appear on invoices')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('company_name')
                                    ->label('Company Name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('company_email')
                                    ->label('Company Email')
                                    ->email()
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('company_phone')
                                    ->label('Company Phone')
                                    ->tel()
                                    ->maxLength(255),

                                TextInput::make('company_website')
                                    ->label('Company Website')
                                    ->url()
                                    ->maxLength(255),
                            ]),

                        Textarea::make('company_address')
                            ->label('Company Address')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Branding')
                    ->description('Upload your company logo and set a tagline for invoices')
                    ->schema([
                        FileUpload::make('company_logo')
                            ->label('Company Logo')
                            ->helperText('Displayed on all generated invoice PDFs. PNG or JPG recommended.')
                            ->image()
                            ->disk('public')
                            ->directory('company-logos')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/gif']),

                        TextInput::make('company_tagline')
                            ->label('Company Tagline')
                            ->placeholder('e.g., Crafting Digital Excellence')
                            ->maxLength(255),
                    ])
                    ->columns(1),

                Section::make('Invoice Defaults')
                    ->description('Default values and behavior for new invoices')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('invoice_prefix')
                                    ->label('Invoice Number Prefix')
                                    ->placeholder('INV')
                                    ->maxLength(10),

                                Select::make('default_currency')
                                    ->label('Default Currency')
                                    ->options([
                                        'IDR' => 'Indonesian Rupiah (IDR)',
                                        'USD' => 'US Dollar (USD)',
                                        'EUR' => 'Euro (EUR)',
                                        'GBP' => 'British Pound (GBP)',
                                        'SGD' => 'Singapore Dollar (SGD)',
                                        'MYR' => 'Malaysian Ringgit (MYR)',
                                    ])
                                    ->default('IDR')
                                    ->required(),

                                TextInput::make('default_payment_terms')
                                    ->label('Default Payment Terms (Days)')
                                    ->numeric()
                                    ->default(30)
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('default_tax_rate')
                                    ->label('Default Tax Rate (%)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->suffix('%'),

                                TextInput::make('late_fee_percentage')
                                    ->label('Late Fee Percentage (%)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->suffix('%'),
                            ]),
                    ])
                    ->columns(3),

                Section::make('Invoice Appearance')
                    ->description('Customize how your invoices look')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('invoice_template')
                                    ->label('Invoice Template')
                                    ->options([
                                        'modern' => 'Modern',
                                        'classic' => 'Classic',
                                        'minimal' => 'Minimal',
                                        'professional' => 'Professional',
                                    ])
                                    ->default('modern'),

                                Select::make('date_format')
                                    ->label('Date Format')
                                    ->options([
                                        'd/m/Y' => 'DD/MM/YYYY',
                                        'm/d/Y' => 'MM/DD/YYYY',
                                        'Y-m-d' => 'YYYY-MM-DD',
                                        'd M Y' => 'DD MMM YYYY',
                                    ])
                                    ->default('d/m/Y'),
                            ]),

                        Textarea::make('invoice_footer_text')
                            ->label('Invoice Footer Text')
                            ->rows(2)
                            ->placeholder('Thank you for your business!')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Automation & Notifications')
                    ->description('Configure automatic actions and notifications')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('auto_send_invoice')
                                    ->label('Auto Send Invoice')
                                    ->helperText('Automatically email invoices when created'),

                                Toggle::make('send_payment_reminders')
                                    ->label('Send Payment Reminders')
                                    ->helperText('Send automatic payment reminder emails'),

                                Toggle::make('auto_follow_up')
                                    ->label('Auto Follow-up')
                                    ->helperText('Send follow-up emails for overdue invoices'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('reminder_days_before')
                                    ->label('Reminder Days Before Due Date')
                                    ->numeric()
                                    ->default(3),

                                TextInput::make('followup_days_after')
                                    ->label('Follow-up Days After Due Date')
                                    ->numeric()
                                    ->default(7),
                            ]),
                    ])
                    ->columns(3),

                Section::make('Bank Information')
                    ->description('Bank accounts for payment information')
                    ->schema([
                        Repeater::make('bank_accounts')
                            ->label('Bank Accounts')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('bank_name')
                                            ->label('Bank Name')
                                            ->required()
                                            ->placeholder('e.g., Bank BCA'),
                                        
                                        TextInput::make('account_number')
                                            ->label('Account Number')
                                            ->required()
                                            ->placeholder('e.g., 1234-5678-9012'),
                                    ]),
                                
                                TextInput::make('account_holder')
                                    ->label('Account Holder Name')
                                    ->required()
                                    ->placeholder('e.g., PT Company Name')
                                    ->columnSpanFull(),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Add Bank Account')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Terms & Conditions')
                    ->description('Default terms and conditions for invoices')
                    ->schema([
                        Textarea::make('default_terms_conditions')
                            ->label('Default Terms & Conditions')
                            ->rows(4)
                            ->placeholder('Payment is due within 30 days of invoice date...')
                            ->columnSpanFull(),

                        Textarea::make('default_notes')
                            ->label('Default Notes')
                            ->rows(3)
                            ->placeholder('Additional notes or instructions...')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            // Encode array data as JSON for bank_accounts
            if ($key === 'bank_accounts') {
                $value = json_encode($value);
            }

            // Skip saving a null logo so existing logo is preserved
            if ($key === 'company_logo' && $value === null) {
                continue;
            }

            InvoiceSettingsModel::setValue(
                $key,
                $value,
                $this->getSettingType($key),
                $this->getSettingDescription($key)
            );
        }

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    protected function getSettingsData(): array
    {
        $settingKeys = [
            'company_name', 'company_email', 'company_phone', 'company_website', 'company_address',
            'company_logo', 'company_tagline',
            'invoice_prefix', 'default_currency', 'default_payment_terms', 'default_tax_rate', 'late_fee_percentage',
            'invoice_template', 'date_format', 'invoice_footer_text',
            'auto_send_invoice', 'send_payment_reminders', 'auto_follow_up', 'reminder_days_before', 'followup_days_after',
            'default_terms_conditions', 'default_notes', 'bank_accounts'
        ];

        $data = [];
        foreach ($settingKeys as $key) {
            $value = InvoiceSettingsModel::getValue($key);
            
            // Convert string booleans to actual booleans for toggles
            if (in_array($key, ['auto_send_invoice', 'send_payment_reminders', 'auto_follow_up'])) {
                $data[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            } elseif ($key === 'bank_accounts') {
                // Decode JSON array for bank accounts
                $data[$key] = $value ? json_decode($value, true) : [];
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    protected function getSettingType(string $key): string
    {
        return match ($key) {
            'company_email' => 'email',
            'company_website' => 'url',
            'company_logo' => 'file',
            'default_payment_terms', 'default_tax_rate', 'late_fee_percentage', 'reminder_days_before', 'followup_days_after' => 'number',
            'auto_send_invoice', 'send_payment_reminders', 'auto_follow_up' => 'boolean',
            'company_address', 'invoice_footer_text', 'default_terms_conditions', 'default_notes' => 'textarea',
            'bank_accounts' => 'json',
            default => 'text'
        };
    }

    protected function getSettingDescription(string $key): string
    {
        return match ($key) {
            'company_name' => 'Company name displayed on invoices',
            'company_email' => 'Primary company email address',
            'company_phone' => 'Company contact phone number',
            'company_website' => 'Company website URL',
            'company_address' => 'Complete company address for invoices',
            'company_logo' => 'Company logo displayed on invoice PDFs',
            'company_tagline' => 'Short tagline shown below the logo on invoices',
            'invoice_prefix' => 'Prefix for invoice numbers (e.g., INV)',
            'default_currency' => 'Default currency for new invoices',
            'default_payment_terms' => 'Default payment terms in days',
            'default_tax_rate' => 'Default tax rate percentage',
            'late_fee_percentage' => 'Late fee percentage for overdue invoices',
            'invoice_template' => 'Default invoice template design',
            'date_format' => 'Date format used in invoices',
            'invoice_footer_text' => 'Text displayed in invoice footer',
            'auto_send_invoice' => 'Automatically send invoices via email when created',
            'send_payment_reminders' => 'Send automatic payment reminder emails',
            'auto_follow_up' => 'Send follow-up emails for overdue invoices',
            'reminder_days_before' => 'Days before due date to send reminder',
            'followup_days_after' => 'Days after due date to send follow-up',
            'default_terms_conditions' => 'Default terms and conditions text',
            'default_notes' => 'Default notes included in invoices',
            'bank_accounts' => 'Bank account information for payment instructions',
            default => 'Invoice setting'
        };
    }
}