<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
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
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('invoice_number')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                Forms\Components\Select::make('user_id')
                                    ->label('Created By')
                                    ->relationship('user', 'name')
                                    ->default(auth()->id())
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),

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
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                static::updateTotals($set, $get);
                            })
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
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                $quantity = floatval($state) ?: 0;
                                                $unitPrice = floatval($get('unit_price')) ?: 0;
                                                $totalPrice = $quantity * $unitPrice;
                                                $set('total_price', $totalPrice);
                                                static::updateTotalsFromItems($set, $get);
                                            }),

                                        Forms\Components\TextInput::make('unit')
                                            ->default('pcs')
                                            ->required(),

                                        Forms\Components\TextInput::make('unit_price')
                                            ->numeric()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                $unitPrice = floatval($state) ?: 0;
                                                $quantity = floatval($get('quantity')) ?: 0;
                                                $totalPrice = $unitPrice * $quantity;
                                                $set('total_price', $totalPrice);
                                                static::updateTotalsFromItems($set, $get);
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

                                Forms\Components\TextInput::make('tax_amount')
                                    ->numeric()
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        static::updateTotals($set, $get);
                                    }),

                                Forms\Components\TextInput::make('total_amount')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->live(),
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
                                    ->live(),

                                Forms\Components\TextInput::make('exchange_rate')
                                    ->numeric()
                                    ->default(1.0000)
                                    ->step(0.0001),
                            ]),
                    ]),

                Section::make('Notes & Terms')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3),

                        Forms\Components\Textarea::make('terms_conditions')
                            ->rows(3),
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
                    Tables\Actions\Action::make('download_pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Invoice $record) {
                            try {
                                $record = $this->record;
                                $record->load(['client', 'project', 'items', 'user']);
                                
                                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', compact('record'))
                                    ->setPaper('a4', 'portrait')
                                    ->setOptions([
                                        'defaultFont' => 'DejaVu Sans',
                                        'isRemoteEnabled' => false,
                                        'isHtml5ParserEnabled' => false, // Disable HTML5 parser
                                        'isFontSubsettingEnabled' => true,
                                        'isPhpEnabled' => false,
                                    ]);
                                
                                return response()->streamDownload(
                                    fn () => print($pdf->output()),
                                    "invoice-{$record->invoice_number}.pdf",
                                    ['Content-Type' => 'application/pdf']
                                );
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('PDF Generation Failed')
                                    ->body('Unable to generate PDF: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                                return null;
                            }
                        }),
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

    public static function updateTotalsFromItems(callable $set, callable $get): void
    {
        // This function is called from item level, we need to get the parent form state
        // In Filament, we need to work with the form's global state
        $items = $get('../../items') ?? [];
        $subtotal = collect($items)->sum(function ($item) {
            return floatval($item['total_price'] ?? 0);
        });
        
        $set('../../subtotal', $subtotal);
        
        // Get other totals
        $discountAmount = floatval($get('../../discount_amount')) ?: 0;
        $taxAmount = floatval($get('../../tax_amount')) ?: 0;
        $totalAmount = $subtotal - $discountAmount + $taxAmount;
        $paidAmount = floatval($get('../../paid_amount')) ?: 0;
        $balanceDue = $totalAmount - $paidAmount;
        
        $set('../../total_amount', $totalAmount);
        $set('../../balance_due', $balanceDue);
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
