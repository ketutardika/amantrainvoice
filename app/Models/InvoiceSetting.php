<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'key', 'value', 'type', 'description'
    ];
}
