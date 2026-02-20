<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;

class EditCompanyProfile extends EditTenantProfile
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function getLabel(): string
    {
        return 'Company Profile';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Company Details')
                    ->description('Update your company information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Company Name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label('Company Email')
                                    ->email()
                                    ->maxLength(255),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('phone')
                                    ->label('Phone Number')
                                    ->tel()
                                    ->maxLength(255),

                                TextInput::make('city')
                                    ->label('City')
                                    ->maxLength(255),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('country')
                                    ->label('Country')
                                    ->default('Indonesia')
                                    ->maxLength(255),

                                TextInput::make('slug')
                                    ->label('Company Slug (URL)')
                                    ->disabled()
                                    ->helperText('Auto-generated. Contact support to change.'),
                            ]),

                        Textarea::make('address')
                            ->label('Address')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
