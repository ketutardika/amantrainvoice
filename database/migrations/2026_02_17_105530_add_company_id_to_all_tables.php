<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that need company_id added.
     */
    private array $tables = [
        'users',
        'invoices',
        'clients',
        'projects',
        'payments',
        'invoice_items',
        'invoice_status_logs',
        'taxes',
        'invoice_settings',
        'invoice_templates',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
                $table->index('company_id');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropForeign([$table === 'taxes' ? 'taxes_company_id_foreign' : 'company_id']);
                $blueprint->dropIndex([$table === 'taxes' ? 'taxes_company_id_index' : 'company_id']);
                $blueprint->dropColumn('company_id');
            });
        }
    }
};
