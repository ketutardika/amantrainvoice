<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_code', 'name', 'company_name', 'email', 'phone',
        'address', 'city', 'state', 'postal_code', 'country',
        'tax_number', 'client_type', 'credit_limit', 'payment_terms',
        'is_active', 'custom_fields'
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'credit_limit' => 'decimal:2',
        'payment_terms' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relations
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function projects() { return $this->hasMany(Project::class); }
    public function payments() { return $this->hasMany(Payment::class); }
}
