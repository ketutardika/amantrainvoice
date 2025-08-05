<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_code', 'name', 'description', 'client_id',
        'status', 'budget', 'start_date', 'end_date'
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function client() { return $this->belongsTo(Client::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
}
