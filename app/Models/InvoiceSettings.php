<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceSettings extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'key',
        'value',
        'type',
        'description'
    ];

    protected $casts = [
        'value' => 'string',
    ];

    public static function getValue(string $key, $default = null, $companyId = null)
    {
        $companyId = $companyId ?? Filament::getTenant()?->id;

        $query = self::where('key', $key);
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $setting = $query->first();
        return $setting ? $setting->value : $default;
    }

    public static function setValue(string $key, $value, string $type = 'text', string $description = null, $companyId = null)
    {
        $companyId = $companyId ?? Filament::getTenant()?->id;

        return self::updateOrCreate(
            ['key' => $key, 'company_id' => $companyId],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description
            ]
        );
    }
}
