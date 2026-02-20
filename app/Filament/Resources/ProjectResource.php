<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
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

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Contacts';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Project Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('project_code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50)
                                    ->default(fn () => 'PRJ-' . date('Y') . '-' . str_pad(Project::where('company_id', Filament::getTenant()?->id)->count() + 1, 4, '0', STR_PAD_LEFT)),

                                Forms\Components\Select::make('client_id')
                                    ->label('Client')
                                    ->relationship('client', 'name', fn (Builder $query) => $query->where('company_id', Filament::getTenant()->id))
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
                            ]),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Project Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'planning' => 'Planning',
                                        'active' => 'Active',
                                        'on_hold' => 'On Hold',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->default('planning')
                                    ->required(),

                                Forms\Components\TextInput::make('budget')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->step(0.01),

                                Forms\Components\TextInput::make('progress_percentage')
                                    ->numeric()
                                    ->label('Progress (%)')
                                    ->suffix('%')
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->default(0),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('start_date')
                                    ->required(),

                                Forms\Components\DatePicker::make('end_date')
                                    ->after('start_date'),
                            ]),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project_code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),

                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'planning',
                        'primary' => 'active',
                        'warning' => 'on_hold',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\TextColumn::make('budget')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->formatStateUsing(fn (?float $state): string => ($state ?? 0) . '%')
                    ->badge()
                    ->color(fn (?float $state): string => match (true) {
                        ($state ?? 0) >= 100 => 'success',
                        ($state ?? 0) >= 75 => 'primary',
                        ($state ?? 0) >= 50 => 'warning',
                        ($state ?? 0) >= 25 => 'info',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('invoices_count')
                    ->label('Invoices')
                    ->counts('invoices')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_invoiced')
                    ->label('Total Invoiced')
                    ->getStateUsing(fn (Project $record) => $record->invoices()->sum('total_amount'))
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('Days Left')
                    ->getStateUsing(function (Project $record) {
                        if (!$record->end_date) return 'No deadline';
                        $days = now()->diffInDays($record->end_date, false);
                        return $days >= 0 ? $days . ' days' : 'Overdue by ' . abs($days) . ' days';
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('export_csv')
                        ->label('Export CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->url(fn () => route('export.data', ['model' => 'projects', 'format' => 'csv']))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('export_xlsx')
                        ->label('Export XLSX')
                        ->icon('heroicon-o-table-cells')
                        ->color('success')
                        ->url(fn () => route('export.data', ['model' => 'projects', 'format' => 'xlsx']))
                        ->openUrlInNewTab(),
                ])
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->button(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'planning' => 'Planning',
                        'active' => 'Active',
                        'on_hold' => 'On Hold',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('client')
                    ->relationship('client', 'name', fn (Builder $query) => $query->where('company_id', Filament::getTenant()->id))
                    ->searchable()
                    ->preload(),

                Filter::make('active_projects')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'active'))
                    ->toggle(),

                Filter::make('overdue_projects')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('end_date', '<', now())
                              ->whereNotIn('status', ['completed', 'cancelled'])
                    )
                    ->toggle(),

                Filter::make('high_budget')
                    ->query(fn (Builder $query): Builder => $query->where('budget', '>', 100000000))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('create_invoice')
                        ->label('Create Invoice')
                        ->icon('heroicon-o-document-plus')
                        ->color('primary')
                        ->action(function (Project $record) {
                            return redirect()->route('filament.admin.resources.invoices.create', [
                                'tenant' => Filament::getTenant(),
                                'client_id' => $record->client_id,
                                'project_id' => $record->id,
                            ]);
                        }),
                    Tables\Actions\Action::make('mark_completed')
                        ->label('Mark as Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Project $record) => !in_array($record->status, ['completed', 'cancelled']))
                        ->action(function (Project $record) {
                            $record->update([
                                'status' => 'completed',
                                'progress_percentage' => 100,
                            ]);
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_active')
                        ->label('Mark as Active')
                        ->icon('heroicon-o-play')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'active']);
                            });
                        })
                        ->requiresConfirmation()
                        ->color('primary'),
                    Tables\Actions\BulkAction::make('mark_completed')
                        ->label('Mark as Completed')
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'status' => 'completed',
                                    'progress_percentage' => 100,
                                ]);
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }    
}
