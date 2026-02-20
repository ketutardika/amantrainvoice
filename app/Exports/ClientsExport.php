<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class ClientsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(protected int $companyId) {}

    public function query(): Builder
    {
        return Client::query()
            ->where('company_id', $this->companyId)
            ->with('invoices')
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Client Code',
            'Name',
            'Company Name',
            'Email',
            'Phone',
            'Type',
            'Address',
            'City',
            'State',
            'Postal Code',
            'Country',
            'Tax Number',
            'Credit Limit (IDR)',
            'Payment Terms (Days)',
            'Total Invoices',
            'Total Revenue (IDR)',
            'Active',
            'Created At',
        ];
    }

    public function map($client): array
    {
        return [
            $client->client_code,
            $client->name,
            $client->company_name,
            $client->email,
            $client->phone,
            ucfirst($client->client_type),
            $client->address,
            $client->city,
            $client->state,
            $client->postal_code,
            $client->country,
            $client->tax_number,
            number_format($client->credit_limit, 2),
            $client->payment_terms,
            $client->invoices->count(),
            number_format($client->invoices->sum('total_amount'), 2),
            $client->is_active ? 'Yes' : 'No',
            $client->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
