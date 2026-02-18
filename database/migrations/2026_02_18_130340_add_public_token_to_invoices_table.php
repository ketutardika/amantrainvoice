<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->uuid('public_token')->nullable()->unique()->after('company_id');
        });

        // Back-fill existing invoices with a unique UUID
        \DB::table('invoices')->whereNull('public_token')->orderBy('id')->each(function ($invoice) {
            \DB::table('invoices')
                ->where('id', $invoice->id)
                ->update(['public_token' => (string) Str::uuid()]);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('public_token');
        });
    }
};
