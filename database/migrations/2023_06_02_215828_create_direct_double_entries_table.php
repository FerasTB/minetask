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
        Schema::create('direct_double_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('COA_id')->nullable()->constrained('c_o_a_s')->onDelete('set null');
            $table->foreignId('direct_double_entry_invoice_id')->nullable()->constrained('direct_double_entry_invoices')->onDelete('set null');
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
        Schema::dropIfExists('direct_double_entries');
    }
};
