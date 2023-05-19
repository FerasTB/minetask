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
        Schema::create('double_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('COA_id')->nullable()->constrained('c_o_a_s')->onDelete('set null');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->foreignId('invoice_item_id')->nullable()->constrained('invoice_items')->onDelete('set null');
            $table->foreignId('receipt_id')->nullable()->constrained('receipts')->onDelete('set null');
            $table->integer('total_price');
            $table->integer("type");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('double_entries');
    }
};
