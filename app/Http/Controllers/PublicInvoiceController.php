<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceStatusLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PublicInvoiceController extends Controller
{
    /**
     * Display the invoice PDF publicly (no authentication required).
     * URL: /invoices/{tenant}/{invoiceNumber}/pdf
     */
    public function show(string $tenant, string $publicToken)
    {
        // Find the company by slug
        $company = Company::where('slug', $tenant)
            ->where('is_active', true)
            ->firstOrFail();

        // Find the invoice by its opaque UUID token, scoped to that company
        $invoice = Invoice::where('company_id', $company->id)
            ->where('public_token', $publicToken)
            ->with(['client', 'project', 'items', 'user'])
            ->firstOrFail();

        // Mark as viewed if the invoice status is 'sent'
        if ($invoice->status === 'sent') {
            $invoice->update([
                'status' => 'viewed',
                'viewed_at' => now(),
            ]);

            InvoiceStatusLog::create([
                'invoice_id'  => $invoice->id,
                'from_status' => 'sent',
                'to_status'   => 'viewed',
                'user_id'     => null,
                'notes'       => 'Invoice viewed by client via public link',
            ]);
        }

        try {
            $pdf = Pdf::loadView('invoices.pdf', ['record' => $invoice])
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont'             => 'DejaVu Sans',
                    'isRemoteEnabled'         => false,
                    'isHtml5ParserEnabled'    => true,
                    'isFontSubsettingEnabled' => false,
                    'isPhpEnabled'            => false,
                    'chroot'                  => public_path(),
                ]);

            $pdfOutput = $pdf->output();

            return response($pdfOutput, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . parse_url(config('app.url'), PHP_URL_HOST) . '-export-invoice-' . $tenant . '-' . strtolower(str_replace(['/', '\\', ' '], '-', $invoice->invoice_number)) . '=' . now()->format('Y-m-d_His') . '.pdf"',
                'Content-Length'      => strlen($pdfOutput),
                'Cache-Control'       => 'no-cache, no-store, must-revalidate',
                'Pragma'              => 'no-cache',
                'Expires'             => '0',
            ]);
        } catch (\Exception $e) {
            abort(500, 'PDF Generation Failed: ' . $e->getMessage());
        }
    }
}
