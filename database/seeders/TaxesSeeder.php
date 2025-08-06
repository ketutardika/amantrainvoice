<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tax;

class TaxesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $taxes = [
            [
                'name' => 'PPN (Pajak Pertambahan Nilai)',
                'code' => 'PPN',
                'rate' => 11.00,
                'type' => 'percentage',
                'description' => 'Indonesian Value Added Tax (VAT) at 11%',
                'is_active' => true
            ],
            [
                'name' => 'PPh Pasal 23',
                'code' => 'PPH23',
                'rate' => 2.00,
                'type' => 'percentage',
                'description' => 'Indonesian Income Tax Article 23 (2% withholding tax)',
                'is_active' => true
            ],
            [
                'name' => 'Service Tax',
                'code' => 'ST',
                'rate' => 6.00,
                'type' => 'percentage',
                'description' => 'Service tax for professional services',
                'is_active' => false
            ]
        ];

        foreach ($taxes as $tax) {
            Tax::updateOrCreate(
                ['code' => $tax['code']],
                $tax
            );
        }
    }
}
