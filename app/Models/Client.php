<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_code',
        'name',
        'company_name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'tax_number',
        'client_type',
        'credit_limit',
        'payment_terms',
        'is_active',
        'custom_fields',
        'avatar',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCompany($query)
    {
        return $query->where('client_type', 'company');
    }

    public function scopeIndividual($query)
    {
        return $query->where('client_type', 'individual');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->company_name ?: $this->name;
    }

    public function getFormattedAddressAttribute()
    {
        $address = collect([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country
        ])->filter()->implode(', ');

        return $address;
    }

    // Auto generate client code
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($client) {
            if (!$client->client_code) {
                $client->client_code = 'CLT-' . str_pad(
                    static::withTrashed()->count() + 1, 
                    4, 
                    '0', 
                    STR_PAD_LEFT
                );
            }
        });
    }
}