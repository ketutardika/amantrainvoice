<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_code')->unique();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Indonesia');
            $table->string('tax_number')->nullable();
            $table->enum('client_type', ['individual', 'company'])->default('company');
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->integer('payment_terms')->default(14);
            $table->boolean('is_active')->default(true);
            $table->json('custom_fields')->nullable();
            $table->string('avatar')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Soft delete support
            
            $table->index(['client_code', 'email']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('clients');
    }
}