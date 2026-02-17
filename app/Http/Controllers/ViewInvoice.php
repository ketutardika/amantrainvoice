<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class ViewInvoice extends Controller
{
    public function __invoke(Request $request, Invoice $invoice)
    {
        // Verify the invoice belongs to the authenticated user's company
        if ($invoice->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        try {
            $invoice->load(['client', 'project', 'items', 'user']);
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', ['record' => $invoice])
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => false,
                    'isHtml5ParserEnabled' => true,
                    'isFontSubsettingEnabled' => false,
                    'isPhpEnabled' => false,
                    'chroot' => public_path(),
                    'debugKeepTemp' => false,
                    'debugCss' => false,
                    'debugLayout' => false,
                    'debugLayoutLines' => false,
                    'debugLayoutBlocks' => false,
                    'debugLayoutInline' => false,
                    'debugLayoutPaddingBox' => false,
                ]);
            
            $pdfOutput = $pdf->output();
            
            return response($pdfOutput, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="invoice-' . $invoice->invoice_number . '.pdf"',
                'Content-Length' => strlen($pdfOutput),
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        } catch (\Exception $e) {
            abort(500, 'PDF Generation Failed: ' . $e->getMessage());
        }
    }
}