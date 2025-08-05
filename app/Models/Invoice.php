<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number', 'client_id', 'project_id', 'user_id',
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

    public function client() { return $this->belongsTo(Client::class); }
    public function project() { return $this->belongsTo(Project::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(InvoiceItem::class)->orderBy('sort_order'); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function statusLogs() { return $this->hasMany(InvoiceStatusLog::class)->orderBy('created_at', 'desc'); }
}
