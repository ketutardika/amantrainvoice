<?php

namespace App\Http\Controllers;

use App\Exports\ClientsExport;
use App\Exports\InvoicesExport;
use App\Exports\ProjectsExport;
use App\Exports\PaymentsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function export(Request $request, string $model)
    {
        $format = $request->get('format', 'xlsx');
        $companyId = auth()->user()->company_id;

        abort_if(!$companyId, 403);

        $siteHost = parse_url(config('app.url'), PHP_URL_HOST);
        $companySlug = auth()->user()->company->slug;
        $timestamp = now()->format('Y-m-d_His');
        $writerType = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;

        return match ($model) {
            'clients' => Excel::download(
                new ClientsExport($companyId),
                "{$siteHost}-export-clients-{$companySlug}-{$timestamp}.{$format}",
                $writerType
            ),
            'invoices' => Excel::download(
                new InvoicesExport($companyId),
                "{$siteHost}-export-invoices-{$companySlug}-{$timestamp}.{$format}",
                $writerType
            ),
            'projects' => Excel::download(
                new ProjectsExport($companyId),
                "{$siteHost}-export-projects-{$companySlug}-{$timestamp}.{$format}",
                $writerType
            ),
            'payments' => Excel::download(
                new PaymentsExport($companyId),
                "{$siteHost}-export-payments-{$companySlug}-{$timestamp}.{$format}",
                $writerType
            ),
            default => abort(404),
        };
    }
}
