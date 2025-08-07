<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Client;
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

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Payment Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('payment_number')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->default(fn () => 'PAY-' . date('Y') . '-' . str_pad(Payment::count() + 1, 5, '0', STR_PAD_LEFT)),

                                Forms\Components\Select::make('invoice_id')
                                    ->label('Invoice')
                                    ->relationship('invoice', 'invoice_number')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $invoice = Invoice::find($state);
                                            if ($invoice) {
                                                $set('client_id', $invoice->client_id);
                                                $set('amount', $invoice->balance_due);
                                            }
                                        }
                                    }),

                                Forms\Components\Select::make('client_id')
                                    ->label('Client')
                                    ->relationship('client', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disabled(fn (callable $get) => !empty($get('invoice_id')))
                                    ->dehydrated(),

                                Forms\Components\Hidden::make('user_id')
                                    ->default(auth()->id()),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->numeric()
                                    ->required()
                                    ->prefix('IDR')
                                    ->step(0.01),

                                Forms\Components\DatePicker::make('payment_date')
                                    ->required()
                                    ->default(now()),

                                Forms\Components\Select::make('payment_method')
                                    ->options([
                                        'cash' => 'Cash',
                                        'bank_transfer' => 'Bank Transfer',
                                        'check' => 'Check',
                                        'credit_card' => 'Credit Card',
                                        'e_wallet' => 'E-Wallet',
                                        'other' => 'Other',
                                    ])
                                    ->required()
                                    ->default('bank_transfer'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('reference_number')
                                    ->maxLength(255)
                                    ->label('Reference/Transaction Number'),

                                Forms\Components\Select::make('status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'verified' => 'Verified',
                                        'failed' => 'Failed',
                                        'refunded' => 'Refunded',
                                    ])
                                    ->default('pending')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Additional Information')
                    ->schema([
                        Forms\Components\FileUpload::make('attachment')
                            ->label('Payment Proof/Receipt')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxSize(5120)
                            ->downloadable(),

                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Verification Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('verified_at')
                                    ->label('Verified At')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\Select::make('verified_by')
                                    ->label('Verified By')
                                    ->relationship('verifiedBy', 'name')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ])
                    ->visible(fn ($record) => $record && $record->verified_at),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_number')
                    ->label('Payment #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Payment $record): string => 
                        $record->invoice ? route('filament.admin.resources.invoices.view', $record->invoice) : '#'
                    ),

                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('payment_method')
                    ->label('Method')
                    ->colors([
                        'success' => 'cash',
                        'primary' => 'bank_transfer',
                        'warning' => 'check',
                        'info' => 'credit_card',
                        'secondary' => 'e_wallet',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'verified',
                        'danger' => 'failed',
                        'gray' => 'refunded',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->toggleable()
                    ->copyable(),

                Tables\Columns\IconColumn::make('attachment')
                    ->label('Proof')
                    ->boolean()
                    ->getStateUsing(fn (Payment $record) => !empty($record->attachment))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Verified')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),

                SelectFilter::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'bank_transfer' => 'Bank Transfer',
                        'check' => 'Check',
                        'credit_card' => 'Credit Card',
                        'e_wallet' => 'E-Wallet',
                        'other' => 'Other',
                    ]),

                SelectFilter::make('client')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('payment_date', today()))
                    ->toggle(),

                Filter::make('this_month')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('payment_date', now()->month))
                    ->toggle(),

                Filter::make('pending_verification')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'pending'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('verify')
                        ->label('Verify Payment')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn (Payment $record) => $record->status === 'pending')
                        ->action(function (Payment $record) {
                            $record->update([
                                'status' => 'verified',
                                'verified_at' => now(),
                                'verified_by' => auth()->id(),
                            ]);

                            // Update invoice status based on payments
                            if ($record->invoice) {
                                $record->invoice->updateStatusBasedOnPayments();
                            }
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\Action::make('download_receipt')
                        ->label('Download Receipt')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->visible(fn (Payment $record) => !empty($record->attachment))
                        ->action(function (Payment $record) {
                            return response()->download(storage_path('app/public/' . $record->attachment));
                        }),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('verify_payments')
                        ->label('Verify Selected')
                        ->icon('heroicon-o-check-badge')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'verified',
                                        'verified_at' => now(),
                                        'verified_by' => auth()->id(),
                                    ]);

                                    // Update invoice status based on payments
                                    if ($record->invoice) {
                                        $record->invoice->updateStatusBasedOnPayments();
                                    }
                                }
                            });
                        })
                        ->requiresConfirmation()
                        ->color('success'),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }    
}
