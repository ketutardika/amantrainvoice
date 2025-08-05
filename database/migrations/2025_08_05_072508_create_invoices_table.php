<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('restrict');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            
            // Invoice Details
            $table->date('invoice_date');
            $table->date('due_date');
            $table->enum('status', [
                'draft', 
                'sent', 
                'viewed', 
                'partial_paid', 
                'paid', 
                'overdue', 
                'cancelled'
            ])->default('draft');
            
            // Financial Data
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);
            
            // Additional Info
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->string('currency', 3)->default('IDR');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            
            // Meta Data
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('email_log')->nullable();
            $table->json('custom_fields')->nullable();
            
            // PDF Storage
            $table->string('pdf_path')->nullable();
            $table->timestamp('pdf_generated_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['invoice_number', 'status', 'due_date']);
            $table->index(['client_id', 'invoice_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}