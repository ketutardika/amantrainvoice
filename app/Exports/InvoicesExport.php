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
            number_format($invoice->subtotal, 2),
            number_format($invoice->discount_amount, 2),
            $invoice->tax ? $invoice->tax->rate . '%' : '0%',
            number_format($invoice->tax_amount, 2),
            number_format($invoice->total_amount, 2),
            number_format($invoice->paid_amount, 2),
            number_format($invoice->balance_due, 2),
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
