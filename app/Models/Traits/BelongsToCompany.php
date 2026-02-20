<?php

namespace App\Models\Traits;

use App\Models\Company;
use Filament\Facades\Filament;

trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::creating(function ($model) {
            if (empty($model->company_id)) {
                $tenant = Filament::getTenant();
                if ($tenant) {
                    $model->company_id = $tenant->id;
                }
            }
        });
    }

    public function initializeBelongsToCompany(): void
    {
        $this->fillable = array_merge($this->fillable, ['company_id']);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
