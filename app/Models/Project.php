<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id', 'project_code', 'name', 'description', 'client_id',
        'status', 'budget', 'progress_percentage', 'start_date', 'end_date'
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function client() { return $this->belongsTo(Client::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
}
