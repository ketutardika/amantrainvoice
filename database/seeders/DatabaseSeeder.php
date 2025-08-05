<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            InvoiceSettingSeeder::class,
            TaxSeeder::class,
            ClientSeeder::class,
            InvoiceTemplateSeeder::class,
        ]);
    }
}