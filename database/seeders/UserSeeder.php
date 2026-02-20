<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();

        User::create([
            'name' => 'Admin',
            'email' => 'admin@amantrainvoice.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => 1,
            'address' => 'Bekasi, Indonesia',
            'phone' => '+62xxxxxxx',
            'company_id' => $company?->id,
        ]);
    }
}
