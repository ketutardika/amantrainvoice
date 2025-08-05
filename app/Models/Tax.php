<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tax extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'rate',
        'type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePercentage($query)
    {
        return $query->where('type', 'percentage');
    }

    // Accessors
    public function getFormattedRateAttribute()
    {
        if ($this->type === 'percentage') {
            return $this->rate . '%';
        }
        return 'Rp ' . number_format($this->rate, 0, ',', '.');
    }
}