<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@amantrainvoice.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+62811-2233-4455',
            'address' => 'Jl. Sudirman No. 123, Jakarta',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Staff User',
            'email' => 'staff@amantrainvoice.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'phone' => '+62812-3344-5566',
            'address' => 'Jl. Thamrin No. 456, Jakarta',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}