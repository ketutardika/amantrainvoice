<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Http\Requests\StorePaymentRequest;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['invoice', 'client', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        $payments = $query->latest('payment_date')->paginate(15);

        return view('payments.index', compact('payments'));
    }

    public function create(Request $request)
    {
        $invoice = null;
        if ($request->filled('invoice_id')) {
            $invoice = Invoice::findOrFail($request->invoice_id);
        }

        $invoices = Invoice::whereIn('status', ['sent', 'viewed', 'partial_paid'])
            ->with('client')
            ->get();

        return view('payments.create', compact('invoice', 'invoices'));
    }

    public function store(StorePaymentRequest $request)
    {
        $invoice = Invoice::findOrFail($request->invoice_id);
        
        $payment = $invoice->addPayment($request->amount, $request->validated());

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment recorded successfully.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['invoice.client', 'user', 'verifiedBy']);
        
        return view('payments.show', compact('payment'));
    }

    public function verify(Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return back()->with('error', 'Only pending payments can be verified.');
        }

        $payment->verify();

        return back()->with('success', 'Payment verified successfully.');
    }

    public function cancel(Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return back()->with('error', 'Only pending payments can be cancelled.');
        }

        $payment->cancel();

        // Adjust invoice amounts
        $invoice = $payment->invoice;
        $invoice->paid_amount -= $payment->amount;
        $invoice->balance_due = $invoice->total_amount - $invoice->paid_amount;
        
        if ($invoice->balance_due > 0) {
            $invoice->status = $invoice->paid_amount > 0 ? 'partial_paid' : 'sent';
        }
        
        $invoice->save();

        return back()->with('success', 'Payment cancelled successfully.');
    }
}