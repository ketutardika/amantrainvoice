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
        return 'Fast. Simple. Reliable. The tool every freelancer and small business needs to get paid instantly';
    }
}