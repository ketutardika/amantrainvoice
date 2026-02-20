<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class PaymentsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(protected int $companyId) {}

    public function query(): Builder
    {
        return Payment::query()
            ->where('company_id', $this->companyId)
            ->with(['invoice', 'client', 'verifiedBy'])
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Payment Number',
            'Invoice Number',
            'Client',
            'Amount (IDR)',
            'Payment Date',
            'Payment Method',
            'Status',
            'Reference Number',
            'Notes',
            'Verified At',
            'Verified By',
            'Created At',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->payment_number,
            $payment->invoice?->invoice_number,
            $payment->client?->name,
            (float) $payment->amount,
            $payment->payment_date?->format('Y-m-d'),
            ucfirst(str_replace('_', ' ', $payment->payment_method)),
            ucfirst($payment->status),
            $payment->reference_number,
            $payment->notes,
            $payment->verified_at?->format('Y-m-d H:i:s'),
            $payment->verifiedBy?->name,
            $payment->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
