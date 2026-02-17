<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
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

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Contacts';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('client_code')
                                    ->label('Client Code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50)
                                    ->default(fn () => Client::generateClientCode(Filament::getTenant()?->id))
                                    ->helperText('Auto-generated but editable: CLT-YYYY-0001 format'),

                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('company_name')
                                    ->maxLength(255),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(20),

                                Forms\Components\Select::make('client_type')
                                    ->options([
                                        'individual' => 'Individual',
                                        'company' => 'Company',
                                    ])
                                    ->default('company')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Address Information')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->rows(3)
                            ->columnSpanFull(),

                        Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('city')
                                    ->maxLength(100),

                                Forms\Components\TextInput::make('state')
                                    ->maxLength(100),

                                Forms\Components\TextInput::make('postal_code')
                                    ->maxLength(20),

                                Forms\Components\TextInput::make('country')
                                    ->default('Indonesia')
                                    ->maxLength(100),
                            ]),
                    ])
                    ->columns(2),

                Section::make('Business Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('tax_number')
                                    ->maxLength(50),

                                Forms\Components\TextInput::make('credit_limit')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('IDR'),

                                Forms\Components\TextInput::make('payment_terms')
                                    ->numeric()
                                    ->default(30)
                                    ->suffix('days')
                                    ->minValue(1)
                                    ->maxValue(365),
                            ]),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Active Status'),
                    ])
                    ->columns(2),

                Section::make('Additional Information')
                    ->schema([
                        Forms\Components\KeyValue::make('custom_fields')
                            ->label('Custom Fields')
                            ->keyLabel('Field Name')
                            ->valueLabel('Value'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client_code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('company_name')
                    ->label('Company')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-phone')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('client_type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'individual',
                        'success' => 'company',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\TextColumn::make('invoices_count')
                    ->label('Invoices')
                    ->counts('invoices')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Total Revenue')
                    ->getStateUsing(fn (Client $record) => $record->invoices()->sum('total_amount'))
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_terms')
                    ->label('Payment Terms')
                    ->formatStateUsing(fn (int $state): string => $state . ' days')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('client_type')
                    ->options([
                        'individual' => 'Individual',
                        'company' => 'Company',
                    ]),

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),

                Filter::make('has_invoices')
                    ->query(fn (Builder $query): Builder => $query->has('invoices'))
                    ->toggle(),

                Filter::make('high_value')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereHas('invoices', function ($q) {
                            $q->havingRaw('SUM(total_amount) > 50000000');
                        })
                    )
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('send_statement')
                        ->label('Send Statement')
                        ->icon('heroicon-o-document-text')
                        ->action(function (Client $record) {
                            // This would integrate with your statement generation
                            return redirect()->route('clients.statement', $record);
                        }),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => true]);
                            });
                        })
                        ->requiresConfirmation()
                        ->color('success'),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_active' => false]);
                            });
                        })
                        ->requiresConfirmation()
                        ->color('danger'),
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }    
}
