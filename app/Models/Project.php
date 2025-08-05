<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_code',
        'name',
        'description',
        'client_id',
        'status',
        'budget',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Auto generate project code
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($project) {
            if (!$project->project_code) {
                $project->project_code = 'PRJ-' . str_pad(
                    static::withTrashed()->count() + 1, 
                    4, 
                    '0', 
                    STR_PAD_LEFT
                );
            }
        });
    }
}