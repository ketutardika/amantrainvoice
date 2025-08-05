<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Tax;
use Illuminate\Http\Request;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['client']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client')) {
            $query->where('client_id', $request->client);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        $invoices = $query->latest('invoice_date')->paginate(15);
        $clients = Client::active()->orderBy('name')->get();

        return view('invoices.index', compact('invoices', 'clients'));
    }

    public function create()
    {
        $clients = Client::active()->orderBy('name')->get();
        $taxes = Tax::active()->get();
        
        return view('invoices.create', compact('clients', 'taxes'));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $invoice = Invoice::create(array_merge($request->validated(), [
            'user_id' => auth()->id(),
        ]));

        // Add invoice items
        foreach ($request->items as $itemData) {
            $invoice->items()->create($itemData);
        }

        $invoice->calculateTotals();

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'payments', 'statusLogs.user']);
        
        // Mark as viewed if accessed by client
        if (request()->has('view') && $invoice->status === 'sent') {
            $invoice->markAsViewed();
        }

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be edited.');
        }

        $invoice->load(['client', 'items']);
        $clients = Client::active()->orderBy('name')->get();
        $taxes = Tax::active()->get();

        return view('invoices.edit', compact('invoice', 'clients', 'taxes'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be updated.');
        }

        $invoice->update($request->validated());

        // Update items
        $invoice->items()->delete();
        foreach ($request->items as $itemData) {
            $invoice->items()->create($itemData);
        }

        $invoice->calculateTotals();

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be deleted.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    public function send(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be sent.');
        }

        $invoice->markAsSent();

        // Here you would typically send email notification
        // dispatch(new SendInvoiceEmailJob($invoice));

        return back()->with('success', 'Invoice sent successfully.');
    }

    public function duplicate(Invoice $invoice)
    {
        $newInvoice = $invoice->replicate();
        $newInvoice->invoice_number = null;
        $newInvoice->status = 'draft';
        $newInvoice->sent_at = null;
        $newInvoice->viewed_at = null;
        $newInvoice->paid_at = null;
        $newInvoice->save();

        // Duplicate items
        foreach ($invoice->items as $item) {
            $newItem = $item->replicate();
            $newItem->invoice_id = $newInvoice->id;
            $newItem->save();
        }

        return redirect()->route('invoices.edit', $newInvoice)
            ->with('success', 'Invoice duplicated successfully.');
    }
}