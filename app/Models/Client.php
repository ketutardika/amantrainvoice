<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id', 'client_code', 'name', 'company_name', 'email', 'phone',
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

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($client) {
            if (empty($client->client_code)) {
                $client->client_code = static::generateClientCode($client->company_id);
            }
        });
    }

    public static function generateClientCode($companyId = null)
    {
        $year = date('Y');
        $query = static::whereYear('created_at', $year);
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        $count = $query->count() + 1;
        return 'CLT-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // Relations
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function projects() { return $this->hasMany(Project::class); }
    public function payments() { return $this->hasMany(Payment::class); }
}
