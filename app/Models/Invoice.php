<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'client_id',
        'project_id',
        'user_id',
        'invoice_date',
        'due_date',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance_due',
        'notes',
        'terms_conditions',
        'currency',
        'exchange_rate',
        'sent_at',
        'viewed_at',
        'paid_at',
        'email_log',
        'custom_fields',
        'pdf_path',
        'pdf_generated_at',
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
        'pdf_generated_at' => 'datetime',
        'email_log' => 'array',
        'custom_fields' => 'array',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(InvoiceStatusLog::class)->orderBy('created_at', 'desc');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->where('balance_due', '>', 0);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('invoice_date', now()->year);
    }

    // Accessors
    public function getIsOverdueAttribute()
    {
        return $this->due_date < now() && $this->balance_due > 0 && $this->status !== 'paid';
    }

    public function getFormattedTotalAttribute()
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getFormattedBalanceAttribute()
    {
        return 'Rp ' . number_format($this->balance_due, 0, ',', '.');
    }

    public function getDaysOverdueAttribute()
    {
        if (!$this->is_overdue) {
            return 0;
        }
        return now()->diffInDays($this->due_date);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'secondary',
            'sent' => 'primary',
            'viewed' => 'info',
            'partial_paid' => 'warning',
            'paid' => 'success',
            'overdue' => 'danger',
            'cancelled' => 'dark'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    // Methods
    public function calculateTotals()
    {
        $this->subtotal = $this->items->sum('total_price');
        $this->balance_due = $this->total_amount - $this->paid_amount;
        $this->save();
    }

    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);

        $this->logStatusChange('draft', 'sent', 'Invoice sent to client');
    }

    public function markAsViewed()
    {
        if ($this->status === 'sent') {
            $this->update([
                'status' => 'viewed',
                'viewed_at' => now()
            ]);

            $this->logStatusChange('sent', 'viewed', 'Invoice viewed by client');
        }
    }

    public function addPayment($amount, $paymentData = [])
    {
        $payment = $this->payments()->create(array_merge($paymentData, [
            'amount' => $amount,
            'client_id' => $this->client_id,
            'user_id' => auth()->id(),
        ]));

        $this->paid_amount += $amount;
        $this->balance_due = $this->total_amount - $this->paid_amount;

        if ($this->balance_due <= 0) {
            $this->status = 'paid';
            $this->paid_at = now();
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial_paid';
        }

        $this->save();
        return $payment;
    }

    public function logStatusChange($fromStatus, $toStatus, $notes = null)
    {
        $this->statusLogs()->create([
            'user_id' => auth()->id(),
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // Auto generate invoice number
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($invoice) {
            if (!$invoice->invoice_number) {
                $year = date('Y');
                $month = date('m');
                $count = static::withTrashed()
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->count() + 1;
                
                $invoice->invoice_number = "INV-{$year}{$month}-" . 
                    str_pad($count, 3, '0', STR_PAD_LEFT);
            }

            if (!$invoice->invoice_date) {
                $invoice->invoice_date = now();
            }

            if (!$invoice->due_date) {
                $paymentTerms = $invoice->client->payment_terms ?? 14;
                $invoice->due_date = now()->addDays($paymentTerms);
            }
        });

        static::saved(function ($invoice) {
            $invoice->calculateTotals();
        });
    }
}