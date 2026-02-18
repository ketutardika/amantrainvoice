<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ViewRecord;

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
                ->url(fn () => route('invoices.public.pdf', [
                    'tenant'        => Filament::getTenant()->slug,
                    'invoiceNumber' => $this->record->invoice_number,
                ]))
                ->openUrlInNewTab(),
        ];
    }

}