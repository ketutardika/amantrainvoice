<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Project;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Invoice Details')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Invoice Number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->default(fn () => Invoice::generateInvoiceNumber(Filament::getTenant()?->id))
                            ->helperText('Auto-generated but editable: {PREFIX}-YYYY-MM-00001 format (configurable in Settings)')
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->id()),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('client_id')
                                    ->label('Client')
                                    ->relationship('client', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->required(),
                                        Forms\Components\TextInput::make('company_name')
                                            ->maxLength(255),
                                    ]),

                                Forms\Components\Select::make('project_id')
                                    ->label('Project')
                                    ->relationship('project', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Grid::make(4)
                            ->schema([
                                Forms\Components\DatePicker::make('invoice_date')
                                    ->required()
                                    ->default(now()),

                                Forms\Components\DatePicker::make('due_date')
                                    ->required()
                                    ->default(now()->addDays(30)),

                                Forms\Components\Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'sent' => 'Sent',
                                        'viewed' => 'Viewed',
                                        'partial_paid' => 'Partially Paid',
                                        'paid' => 'Paid',
                                        'overdue' => 'Overdue',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->default('draft')
                                    ->required(),

                                Forms\Components\TextInput::make('currency')
                                    ->default('IDR')
                                    ->required()
                                    ->maxLength(3),
                            ]),
                    ]),

                Section::make('Invoice Items')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                static::updateTotals($set, $get);
                            })
                            ->addAction(
                                fn ($action) => $action->after(function (callable $set, callable $get) {
                                    static::updateTotals($set, $get);
                                })
                            )
                            ->deleteAction(
                                fn ($action) => $action->after(function (callable $set, callable $get) {
                                    static::updateTotals($set, $get);
                                })
                            )
                            ->reorderAction(
                                fn ($action) => $action->after(function (callable $set, callable $get) {
                                    static::updateTotals($set, $get);
                                })
                            )
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->columnSpan(2),

                                        Forms\Components\TextInput::make('quantity')
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                $quantity = floatval($state) ?: 0;
                                                $unitPrice = floatval($get('unit_price')) ?: 0;
                                                $totalPrice = $quantity * $unitPrice;
                                                $set('total_price', $totalPrice);
                                                
                                                // Update parent form totals
                                                $items = $get('../../items') ?? [];
                                                $subtotal = collect($items)->sum(function ($item) use ($totalPrice, $get) {
                                                    if ($item === $get('../')) {
                                                        return $totalPrice; // Use updated value for current item
                                                    }
                                                    return floatval($item['total_price'] ?? 0);
                                                });
                                                
                                                $discountAmount = floatval($get('../../discount_amount')) ?: 0;
                                                $taxAmount = floatval($get('../../tax_amount')) ?: 0;
                                                $paidAmount = floatval($get('../../paid_amount')) ?: 0;
                                                
                                                $totalAmount = $subtotal - $discountAmount + $taxAmount;
                                                $balanceDue = $totalAmount - $paidAmount;
                                                
                                                $set('../../subtotal', $subtotal);
                                                $set('../../total_amount', $totalAmount);
                                                $set('../../balance_due', $balanceDue);
                                            }),

                                        Forms\Components\Select::make('unit')
                                            ->options([
                                                'pcs' => 'Pieces',
                                                'hours' => 'Hours',
                                                'days' => 'Days',
                                                'package' => 'Package',
                                                'month' => 'Month',
                                                'year' => 'Year',
                                            ])
                                            ->default('pcs')
                                            ->required(),

                                        Forms\Components\TextInput::make('unit_price')
                                            ->numeric()
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                $unitPrice = floatval($state) ?: 0;
                                                $quantity = floatval($get('quantity')) ?: 0;
                                                $totalPrice = $unitPrice * $quantity;
                                                $set('total_price', $totalPrice);
                                                
                                                // Update parent form totals
                                                $items = $get('../../items') ?? [];
                                                $subtotal = collect($items)->sum(function ($item) use ($totalPrice, $get) {
                                                    if ($item === $get('../')) {
                                                        return $totalPrice; // Use updated value for current item
                                                    }
                                                    return floatval($item['total_price'] ?? 0);
                                                });
                                                
                                                $discountAmount = floatval($get('../../discount_amount')) ?: 0;
                                                $taxAmount = floatval($get('../../tax_amount')) ?: 0;
                                                $paidAmount = floatval($get('../../paid_amount')) ?: 0;
                                                
                                                $totalAmount = $subtotal - $discountAmount + $taxAmount;
                                                $balanceDue = $totalAmount - $paidAmount;
                                                
                                                $set('../../subtotal', $subtotal);
                                                $set('../../total_amount', $totalAmount);
                                                $set('../../balance_due', $balanceDue);
                                            }),

                                        Forms\Components\TextInput::make('total_price')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->live(),
                                    ]),

                                Forms\Components\Textarea::make('description')
                                    ->rows(2),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Add Item')
                            ->columnSpanFull(),
                    ]),

                Section::make('Totals & Payment')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('subtotal')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->live()
                                    ->afterStateHydrated(function ($component, $state, callable $get) {
                                        $items = $get('items') ?? [];
                                        $subtotal = collect($items)->sum('total_price');
                                        $component->state($subtotal);
                                    }),

                                Forms\Components\TextInput::make('discount_amount')
                                    ->numeric()
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        static::updateTotals($set, $get);
                                    }),

                                Forms\Components\Select::make('tax_id')
                                    ->label('Tax')
                                    ->relationship('tax', 'name')
                                    ->options(function () {
                                        return \App\Models\Tax::where('is_active', true)
                                            ->where('company_id', Filament::getTenant()?->id)
                                            ->get()
                                            ->pluck('name', 'id')
                                            ->map(fn ($name, $id) => "{$name} (" . \App\Models\Tax::find($id)?->rate . "%)");
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state) {
                                            $tax = \App\Models\Tax::where('id', $state)
                                                ->where('company_id', Filament::getTenant()?->id)
                                                ->first();
                                            if ($tax) {
                                                $subtotal = floatval($get('subtotal')) ?: 0;
                                                $taxAmount = ($subtotal * $tax->rate) / 100;
                                                $set('tax_amount', $taxAmount);
                                            }
                                        } else {
                                            $set('tax_amount', 0);
                                        }
                                        static::updateTotals($set, $get);
                                    }),

                                Forms\Components\TextInput::make('tax_amount')
                                    ->label('Tax Amount')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->live()
                                    ->default(0),

                                Forms\Components\TextInput::make('total_amount')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->live()
                                    ->afterStateHydrated(function ($component, $state, callable $get) {
                                        $items = $get('items') ?? [];
                                        $subtotal = collect($items)->sum('total_price');
                                        $discountAmount = floatval($get('discount_amount')) ?: 0;
                                        $taxAmount = floatval($get('tax_amount')) ?: 0;
                                        $totalAmount = $subtotal - $discountAmount + $taxAmount;
                                        $component->state($totalAmount);
                                    }),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('paid_amount')
                                    ->numeric()
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        static::updateTotals($set, $get);
                                    }),

                                Forms\Components\TextInput::make('balance_due')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->live()
                                    ->afterStateHydrated(function ($component, $state, callable $get) {
                                        $items = $get('items') ?? [];
                                        $subtotal = collect($items)->sum('total_price');
                                        $discountAmount = floatval($get('discount_amount')) ?: 0;
                                        $taxAmount = floatval($get('tax_amount')) ?: 0;
                                        $paidAmount = floatval($get('paid_amount')) ?: 0;
                                        $totalAmount = $subtotal - $discountAmount + $taxAmount;
                                        $balanceDue = $totalAmount - $paidAmount;
                                        $component->state($balanceDue);
                                    }),

                                Forms\Components\TextInput::make('exchange_rate')
                                    ->numeric()
                                    ->default(1.0000)
                                    ->step(0.0001),
                            ]),
                    ]),

                Section::make('Notes & Terms')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->default(fn () => \App\Models\InvoiceSettings::getValue('default_notes')),

                        Forms\Components\Textarea::make('terms_conditions')
                            ->rows(3)
                            ->default(fn () => \App\Models\InvoiceSettings::getValue('default_terms_conditions')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'draft',
                        'primary' => 'sent',
                        'info' => 'viewed',
                        'warning' => 'partial_paid',
                        'success' => 'paid',
                        'danger' => 'overdue',
                        'gray' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('balance_due')
                    ->label('Balance Due')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Sent',
                        'viewed' => 'Viewed',
                        'partial_paid' => 'Partially Paid',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('client')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('overdue')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'overdue'))
                    ->toggle(),

                Filter::make('this_month')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('invoice_date', now()->month))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('test')
                        ->label('Test Action')
                        ->icon('heroicon-o-check')
                        ->action(function() {
                            \Filament\Notifications\Notification::make()
                                ->title('Test works!')
                                ->send();
                        }),
                    Tables\Actions\Action::make('make_payment')
                        ->label('Make Payment')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn (Invoice $record) => !in_array($record->status, ['paid', 'cancelled']))
                        ->form([
                            Forms\Components\TextInput::make('payment_number')
                                ->required()
                                ->unique(Payment::class, 'payment_number')
                                ->maxLength(255)
                                ->default(fn () => 'PAY-' . date('Y') . '-' . str_pad(Payment::where('company_id', Filament::getTenant()?->id)->count() + 1, 5, '0', STR_PAD_LEFT)),

                            Forms\Components\Hidden::make('invoice_id'),

                            Forms\Components\Hidden::make('client_id'),

                            Forms\Components\Hidden::make('user_id')
                                ->default(auth()->id()),

                            Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('amount')
                                        ->numeric()
                                        ->required()
                                        ->prefix('IDR')
                                        ->step(0.01)
                                        ->default(function (callable $get, $livewire) {
                                            $record = $livewire->record ?? null;
                                            return $record ? ($record->balance_due ?? $record->total_amount) : 0;
                                        }),

                                    Forms\Components\DatePicker::make('payment_date')
                                        ->required()
                                        ->default(now()),
                                ]),

                            Grid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('payment_method')
                                        ->options([
                                            'cash' => 'Cash',
                                            'bank_transfer' => 'Bank Transfer',
                                            'credit_card' => 'Credit Card',
                                            'debit_card' => 'Debit Card',
                                            'gopay' => 'GoPay',
                                            'ovo' => 'OVO',
                                            'dana' => 'Dana',
                                            'shopeepay' => 'ShopeePay',
                                            'other' => 'Other',
                                        ])
                                        ->required()
                                        ->default('bank_transfer'),

                                    Forms\Components\TextInput::make('reference_number')
                                        ->maxLength(255)
                                        ->label('Reference/Transaction Number'),
                                ]),

                            Forms\Components\Select::make('status')
                                ->options([
                                    'pending' => 'Pending',
                                    'verified' => 'Verified',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->default('pending')
                                ->required(),

                            Forms\Components\FileUpload::make('attachment')
                                ->label('Payment Proof/Receipt')
                                ->acceptedFileTypes(['image/*', 'application/pdf'])
                                ->maxSize(5120)
                                ->downloadable(),

                            Forms\Components\Textarea::make('notes')
                                ->rows(3)
                                ->columnSpanFull(),
                        ])
                        ->action(function (Invoice $record, array $data) {
                            // Set the invoice, client, and company ID
                            $data['invoice_id'] = $record->id;
                            $data['client_id'] = $record->client_id;
                            $data['company_id'] = Filament::getTenant()->id;

                            // Create the payment
                            $payment = Payment::create($data);

                            // If payment is verified, update invoice status
                            if ($payment->status === 'verified') {
                                $record->updateStatusBasedOnPayments();
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Payment Created Successfully')
                                ->body("Payment #{$payment->payment_number} has been created.")
                                ->success()
                                ->send();
                        })
                        ->modalHeading('Create Payment')
                        ->modalWidth('lg'),
                    Tables\Actions\Action::make('view_pdf')
                        ->label('View PDF')
                        ->icon('heroicon-o-eye')
                        ->url(fn (Invoice $record): string => route('invoices.public.pdf', [
                            'tenant'        => Filament::getTenant()->slug,
                            'invoiceNumber' => $record->invoice_number,
                        ]))
                        ->openUrlInNewTab(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_as_sent')
                        ->label('Mark as Sent')
                        ->icon('heroicon-o-paper-airplane')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'sent', 'sent_at' => now()]);
                            });
                        })
                        ->requiresConfirmation()
                        ->color('primary'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    
    public static function updateTotals(callable $set, callable $get): void
    {
        $items = $get('items') ?? [];
        $subtotal = collect($items)->sum(function ($item) {
            return floatval($item['total_price'] ?? 0);
        });
        
        $discountAmount = floatval($get('discount_amount')) ?: 0;
        $taxAmount = floatval($get('tax_amount')) ?: 0;
        $paidAmount = floatval($get('paid_amount')) ?: 0;
        
        $totalAmount = $subtotal - $discountAmount + $taxAmount;
        $balanceDue = $totalAmount - $paidAmount;
        
        $set('subtotal', $subtotal);
        $set('total_amount', $totalAmount);
        $set('balance_due', $balanceDue);
    }    
}
