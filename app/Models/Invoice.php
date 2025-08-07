<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number', 'client_id', 'project_id', 'user_id', 'tax_id',
        'invoice_date', 'due_date', 'status',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount',
        'paid_amount', 'balance_due',
        'notes', 'terms_conditions', 'currency', 'exchange_rate',
        'sent_at', 'viewed_at', 'paid_at', 'email_log', 'custom_fields'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'paid_at' => 'datetime',
        'email_log' => 'array',
        'custom_fields' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber()
    {
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $invoicePrefix = InvoiceSettings::getValue('invoice_prefix', 'INV');
        $prefix = "{$invoicePrefix}-{$year}-{$month}-";
        
        // Find the latest invoice number for current month
        $latestInvoice = static::where('invoice_number', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(invoice_number, -5) AS UNSIGNED) DESC')
            ->first();
            
        if ($latestInvoice) {
            // Extract the last 5 digits and increment
            $lastNumber = (int) substr($latestInvoice->invoice_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    public function client() { return $this->belongsTo(Client::class); }
    public function project() { return $this->belongsTo(Project::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function tax() { return $this->belongsTo(Tax::class); }
    public function items() { return $this->hasMany(InvoiceItem::class)->orderBy('sort_order'); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function statusLogs() { return $this->hasMany(InvoiceStatusLog::class)->orderBy('created_at', 'desc'); }

    public function updateStatusBasedOnPayments()
    {
        // Calculate total verified payments
        $verifiedPayments = $this->payments()
            ->where('status', 'verified')
            ->sum('amount');

        // Update paid_amount and balance_due
        $this->update([
            'paid_amount' => $verifiedPayments,
            'balance_due' => $this->total_amount - $verifiedPayments,
        ]);

        // Determine new status based on payment
        $newStatus = $this->determineStatusFromPayments($verifiedPayments);

        // Update status if it has changed
        if ($this->status !== $newStatus) {
            $oldStatus = $this->status;
            
            $this->update(['status' => $newStatus]);

            // Set paid_at timestamp if fully paid
            if ($newStatus === 'paid' && !$this->paid_at) {
                $this->update(['paid_at' => now()]);
            }

            // Create status log entry
            InvoiceStatusLog::create([
                'invoice_id' => $this->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'user_id' => auth()->id(),
                'notes' => "Status updated due to payment verification. Paid: " . number_format($verifiedPayments, 2),
            ]);
        }
    }

    private function determineStatusFromPayments($paidAmount)
    {
        if ($paidAmount >= $this->total_amount) {
            return 'paid';
        } elseif ($paidAmount > 0) {
            return 'partial_paid';
        } elseif ($this->due_date && $this->due_date->isPast()) {
            return 'overdue';
        } elseif ($this->sent_at) {
            return 'sent';
        } else {
            return 'draft';
        }
    }
}
