<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_id', 'item_type', 'name', 'description',
        'quantity', 'unit', 'unit_price', 'total_price',
        'sort_order', 'meta_data'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'meta_data' => 'array',
    ];

    public function invoice() { return $this->belongsTo(Invoice::class); }
}
