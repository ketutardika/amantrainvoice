<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class ProjectsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(protected int $companyId) {}

    public function query(): Builder
    {
        return Project::query()
            ->where('company_id', $this->companyId)
            ->with(['client', 'invoices'])
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Project Code',
            'Name',
            'Client',
            'Status',
            'Budget (IDR)',
            'Progress (%)',
            'Total Invoices',
            'Total Invoiced (IDR)',
            'Start Date',
            'End Date',
            'Description',
            'Created At',
        ];
    }

    public function map($project): array
    {
        return [
            $project->project_code,
            $project->name,
            $project->client?->name,
            ucfirst(str_replace('_', ' ', $project->status)),
            number_format($project->budget, 2),
            $project->progress_percentage . '%',
            $project->invoices->count(),
            number_format($project->invoices->sum('total_amount'), 2),
            $project->start_date?->format('Y-m-d'),
            $project->end_date?->format('Y-m-d'),
            $project->description,
            $project->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
