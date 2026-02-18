<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\URL;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('view_pdf')
                ->label('View PDF')
                ->icon('heroicon-o-document-text')
                ->url(fn () => URL::signedRoute('invoices.public.pdf', [
                    'tenant'      => Filament::getTenant()->slug,
                    'publicToken' => $this->record->public_token,
                ]))
                ->openUrlInNewTab(),
        ];
    }

}