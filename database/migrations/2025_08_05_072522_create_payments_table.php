<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('invoice_id')->constrained()->onDelete('restrict');
            $table->foreignId('client_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->enum('payment_method', [
                'bank_transfer',
                'cash',
                'credit_card',
                'debit_card',
                'gopay',
                'ovo',
                'dana',
                'shopeepay',
                'other'
            ]);
            
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment')->nullable();
            
            $table->enum('status', ['pending', 'verified', 'cancelled'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['payment_number', 'payment_date']);
            $table->index(['invoice_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}