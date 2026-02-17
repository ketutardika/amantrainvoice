<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::create([
            'name' => 'Demo Company',
            'slug' => 'demo-company',
            'email' => 'info@company.com',
            'phone' => '+62xxx-xxxx-xxxx',
            'address' => 'Bekasi, Indonesia',
            'country' => 'Indonesia',
            'is_active' => true,
        ]);
    }
}
