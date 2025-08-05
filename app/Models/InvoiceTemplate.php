<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'html_template',
        'css_styles', 'is_default', 'is_active'
    ];

    protected $casts = [
        'css_styles' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];
}
