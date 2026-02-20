<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Invoice Dashboard';
    protected static ?string $navigationLabel = 'Dashboard';
    
    public function getHeading(): string
    {
        return 'Invoice Management System';
    }
    
    public function getSubheading(): ?string
    {
        return 'Create professional invoices instantly and get paid faster.';
    }
}