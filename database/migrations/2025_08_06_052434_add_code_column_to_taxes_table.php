<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('taxes', 'code')) {
            Schema::table('taxes', function (Blueprint $table) {
                $table->string('name')->after('id');
                $table->string('code')->unique()->after('name');
                $table->decimal('rate', 5, 2)->after('code');
                $table->enum('type', ['percentage', 'fixed'])->default('percentage')->after('rate');
                $table->text('description')->nullable()->after('type');
                $table->boolean('is_active')->default(true)->after('description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropColumn(['name', 'code', 'rate', 'type', 'description', 'is_active']);
        });
    }
};
