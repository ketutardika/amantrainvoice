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
                'code' => 'PPN11',
                'rate' => 11.00,
                'type' => 'percentage',
                'description' => 'Pajak Pertambahan Nilai 11%',
                'is_active' => true
            ],
            [
                'name' => 'PPh Pasal 23',
                'code' => 'PPH23',
                'rate' => 2.00,
                'type' => 'percentage',
                'description' => 'Pajak Penghasilan Pasal 23 (2%)',
                'is_active' => false
            ]
        ];

        foreach ($taxes as $tax) {
            Tax::create($tax);
        }
    }
}
