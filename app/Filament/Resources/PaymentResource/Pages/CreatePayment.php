<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If invoice is selected but client_id is not set, get it from the invoice
        if (!empty($data['invoice_id']) && empty($data['client_id'])) {
            $invoice = Invoice::where('id', $data['invoice_id'])
                ->where('company_id', Filament::getTenant()?->id)
                ->first();
            if ($invoice) {
                $data['client_id'] = $invoice->client_id;
            }
        }

        // Set user_id to current authenticated user if not set
        if (empty($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }

        return $data;
    }
}
