<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('download_pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
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
        ];
    }

}