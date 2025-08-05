<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PDFController extends Controller
{
    public function generateInvoice(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'user']);
        
        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        // Store PDF path
        $fileName = "invoice-{$invoice->invoice_number}.pdf";
        $filePath = "invoices/{$fileName}";
        
        \Storage::disk('public')->put($filePath, $pdf->output());
        
        $invoice->update([
            'pdf_path' => $filePath,
            'pdf_generated_at' => now()
        ]);

        return $pdf->download($fileName);
    }

    public function previewInvoice(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'user']);
        
        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    }

    public function downloadStoredInvoice(Invoice $invoice)
    {
        if (!$invoice->pdf_path || !\Storage::disk('public')->exists($invoice->pdf_path)) {
            return $this->generateInvoice($invoice);
        }

        $fileName = "invoice-{$invoice->invoice_number}.pdf";
        return \Storage::disk('public')->download($invoice->pdf_path, $fileName);
    }
}