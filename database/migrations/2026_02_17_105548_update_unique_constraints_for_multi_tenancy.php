<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // clients: client_code unique → (company_id, client_code) unique
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['client_code']);
            $table->unique(['company_id', 'client_code']);
        });

        // projects: project_code unique → (company_id, project_code) unique
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['project_code']);
            $table->unique(['company_id', 'project_code']);
        });

        // invoices: invoice_number unique → (company_id, invoice_number) unique
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['invoice_number']);
            $table->unique(['company_id', 'invoice_number']);
        });

        // payments: payment_number unique → (company_id, payment_number) unique
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['payment_number']);
            $table->unique(['company_id', 'payment_number']);
        });

        // taxes: code unique → (company_id, code) unique
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->unique(['company_id', 'code']);
        });

        // invoice_templates: slug unique → (company_id, slug) unique
        Schema::table('invoice_templates', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->unique(['company_id', 'slug']);
        });

        // invoice_settings: key unique → (company_id, key) unique
        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->dropUnique(['key']);
            $table->unique(['company_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'client_code']);
            $table->unique('client_code');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'project_code']);
            $table->unique('project_code');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'invoice_number']);
            $table->unique('invoice_number');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'payment_number']);
            $table->unique('payment_number');
        });

        Schema::table('taxes', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'code']);
            $table->unique('code');
        });

        Schema::table('invoice_templates', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'slug']);
            $table->unique('slug');
        });

        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'key']);
            $table->unique('key');
        });
    }
};
