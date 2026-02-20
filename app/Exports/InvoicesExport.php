<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class InvoicesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(protected int $companyId) {}

    public function query(): Builder
    {
        return Invoice::query()
            ->where('company_id', $this->companyId)
            ->with(['client', 'project', 'tax'])
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Invoice Number',
            'Client',
            'Project',
            'Status',
            'Invoice Date',
            'Due Date',
            'Currency',
            'Subtotal (IDR)',
            'Discount (IDR)',
            'Tax (%)',
            'Tax Amount (IDR)',
            'Total Amount (IDR)',
            'Paid Amount (IDR)',
            'Balance Due (IDR)',
            'Notes',
            'Created At',
        ];
    }

    public function map($invoice): array
    {
        return [
            $invoice->invoice_number,
            $invoice->client?->name,
            $invoice->project?->name,
            ucfirst(str_replace('_', ' ', $invoice->status)),
            $invoice->invoice_date?->format('Y-m-d'),
            $invoice->due_date?->format('Y-m-d'),
            $invoice->currency,
            (float) $invoice->subtotal,
            (float) $invoice->discount_amount,
            $invoice->tax ? $invoice->tax->rate . '%' : '0%',
            (float) $invoice->tax_amount,
            (float) $invoice->total_amount,
            (float) $invoice->paid_amount,
            (float) $invoice->balance_due,
            $invoice->notes,
            $invoice->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
