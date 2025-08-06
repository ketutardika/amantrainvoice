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
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use App\Models\Tax;

class TaxSettings extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.tax-settings';
    protected static ?string $title = 'Tax Settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getTaxesData());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Tax Configuration')
                    ->description('Manage all tax rates and settings used in invoices')
                    ->schema([
                        Repeater::make('taxes')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->placeholder('e.g., Value Added Tax')
                                            ->columnSpan(1),

                                        TextInput::make('code')
                                            ->required()
                                            ->placeholder('e.g., VAT, PPN')
                                            ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                                            ->columnSpan(1),

                                        TextInput::make('rate')
                                            ->numeric()
                                            ->required()
                                            ->suffix('%')
                                            ->step(0.01)
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->placeholder('11.00')
                                            ->columnSpan(1),

                                        Select::make('type')
                                            ->options([
                                                'percentage' => 'Percentage',
                                                'fixed' => 'Fixed Amount',
                                                'compound' => 'Compound',
                                            ])
                                            ->default('percentage')
                                            ->required()
                                            ->columnSpan(1),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        Textarea::make('description')
                                            ->rows(2)
                                            ->placeholder('Description of when this tax applies')
                                            ->columnSpan(1),

                                        Toggle::make('is_active')
                                            ->label('Active')
                                            ->default(true)
                                            ->helperText('Only active taxes will be available for selection')
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Add New Tax')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'New Tax')
                            ->columnSpanFull()
                            ->minItems(0),
                    ]),

                Section::make('Common Tax Rates')
                    ->description('Quick setup for commonly used tax rates')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('quick_add')
                                    ->label('Add Common Tax')
                                    ->placeholder('Select a common tax to add')
                                    ->options([
                                        'vat_standard' => 'VAT Standard Rate (11%)',
                                        'vat_reduced' => 'VAT Reduced Rate (5%)',
                                        'sales_tax' => 'Sales Tax (10%)',
                                        'gst' => 'GST (10%)',
                                        'service_tax' => 'Service Tax (6%)',
                                    ])
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state) {
                                            $this->addCommonTax($state, $set, $get);
                                        }
                                    }),
                            ]),
                    ])
                    ->columns(3),

                Section::make('Tax Calculation Settings')
                    ->description('Configure how taxes are calculated and applied')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('default_tax_calculation')
                                    ->label('Default Tax Calculation Method')
                                    ->options([
                                        'exclusive' => 'Tax Exclusive (add tax to subtotal)',
                                        'inclusive' => 'Tax Inclusive (tax included in price)',
                                    ])
                                    ->default('exclusive')
                                    ->helperText('How taxes are calculated by default'),

                                Toggle::make('compound_taxes_enabled')
                                    ->label('Enable Compound Taxes')
                                    ->helperText('Allow taxes to be calculated on top of other taxes')
                                    ->default(false),

                                Toggle::make('round_tax_calculations')
                                    ->label('Round Tax Calculations')
                                    ->helperText('Round tax amounts to 2 decimal places')
                                    ->default(true),
                            ]),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Tax Settings')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Delete all existing taxes first (soft delete)
        Tax::query()->delete();

        // Save new taxes
        if (!empty($data['taxes'])) {
            foreach ($data['taxes'] as $taxData) {
                if (!empty($taxData['name']) && !empty($taxData['code'])) {
                    Tax::create([
                        'name' => $taxData['name'],
                        'code' => strtoupper($taxData['code']),
                        'rate' => $taxData['rate'] ?? 0,
                        'type' => $taxData['type'] ?? 'percentage',
                        'description' => $taxData['description'] ?? null,
                        'is_active' => $taxData['is_active'] ?? true,
                    ]);
                }
            }
        }

        // Save tax calculation settings as invoice settings
        if (class_exists(\App\Models\InvoiceSettings::class)) {
            \App\Models\InvoiceSettings::setValue('default_tax_calculation', $data['default_tax_calculation'] ?? 'exclusive', 'text', 'Default tax calculation method');
            \App\Models\InvoiceSettings::setValue('compound_taxes_enabled', $data['compound_taxes_enabled'] ? 'true' : 'false', 'boolean', 'Enable compound taxes');
            \App\Models\InvoiceSettings::setValue('round_tax_calculations', $data['round_tax_calculations'] ? 'true' : 'false', 'boolean', 'Round tax calculations');
        }

        Notification::make()
            ->title('Tax settings saved successfully')
            ->success()
            ->send();
    }

    protected function getTaxesData(): array
    {
        $taxes = Tax::orderBy('name')->get();
        
        $data = [
            'taxes' => $taxes->map(function ($tax) {
                return [
                    'name' => $tax->name,
                    'code' => $tax->code,
                    'rate' => $tax->rate,
                    'type' => $tax->type,
                    'description' => $tax->description,
                    'is_active' => $tax->is_active,
                ];
            })->toArray(),
            'default_tax_calculation' => 'exclusive',
            'compound_taxes_enabled' => false,
            'round_tax_calculations' => true,
        ];

        // Load tax calculation settings if they exist
        if (class_exists(\App\Models\InvoiceSettings::class)) {
            $data['default_tax_calculation'] = \App\Models\InvoiceSettings::getValue('default_tax_calculation', 'exclusive');
            $data['compound_taxes_enabled'] = filter_var(\App\Models\InvoiceSettings::getValue('compound_taxes_enabled', 'false'), FILTER_VALIDATE_BOOLEAN);
            $data['round_tax_calculations'] = filter_var(\App\Models\InvoiceSettings::getValue('round_tax_calculations', 'true'), FILTER_VALIDATE_BOOLEAN);
        }

        return $data;
    }

    protected function addCommonTax($type, callable $set, callable $get): void
    {
        $commonTaxes = [
            'vat_standard' => [
                'name' => 'Value Added Tax',
                'code' => 'VAT',
                'rate' => 11.00,
                'type' => 'percentage',
                'description' => 'Standard VAT rate for most goods and services',
            ],
            'vat_reduced' => [
                'name' => 'VAT Reduced Rate',
                'code' => 'VAT_R',
                'rate' => 5.00,
                'type' => 'percentage',
                'description' => 'Reduced VAT rate for essential goods',
            ],
            'sales_tax' => [
                'name' => 'Sales Tax',
                'code' => 'ST',
                'rate' => 10.00,
                'type' => 'percentage',
                'description' => 'General sales tax',
            ],
            'gst' => [
                'name' => 'Goods and Services Tax',
                'code' => 'GST',
                'rate' => 10.00,
                'type' => 'percentage',
                'description' => 'Goods and Services Tax',
            ],
            'service_tax' => [
                'name' => 'Service Tax',
                'code' => 'SRV',
                'rate' => 6.00,
                'type' => 'percentage',
                'description' => 'Tax applied to services',
            ],
        ];

        if (isset($commonTaxes[$type])) {
            $currentTaxes = $get('taxes') ?? [];
            $newTax = $commonTaxes[$type];
            $newTax['is_active'] = true;
            
            $currentTaxes[] = $newTax;
            $set('taxes', $currentTaxes);
        }

        // Reset the select
        $set('quick_add', null);
    }
}