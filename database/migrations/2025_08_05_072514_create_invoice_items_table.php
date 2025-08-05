<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceItemsTable extends Migration
{
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            
            $table->string('item_type')->default('service');
            $table->string('name');
            $table->text('description')->nullable();
            
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit')->default('pcs');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            
            $table->integer('sort_order')->default(0);
            $table->json('meta_data')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['invoice_id', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_items');
    }
}
