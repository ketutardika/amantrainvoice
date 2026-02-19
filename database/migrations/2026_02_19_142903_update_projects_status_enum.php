<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `projects` MODIFY COLUMN `status` ENUM('planning', 'active', 'on_hold', 'completed', 'cancelled') NOT NULL DEFAULT 'planning'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `projects` MODIFY COLUMN `status` ENUM('draft', 'active', 'completed', 'cancelled') NOT NULL DEFAULT 'draft'");
    }
};
