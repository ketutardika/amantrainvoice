<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::create([
            'name' => 'Amantra Future Technology',
            'slug' => 'amantra',
            'email' => 'info@amantrabali.com',
            'phone' => '+62812-3677-2522',
            'address' => 'Jl. Indrakila 6 No 11, Denpasar - Bali',
            'country' => 'Indonesia',
            'is_active' => true,
        ]);
    }
}
