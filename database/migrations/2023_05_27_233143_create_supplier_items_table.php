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
        Schema::create('supplier_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('COA_id')->constrained('c_o_a_s')->onDelete('cascade');
            $table->foreignId('office_id')->nullable()->constrained('offices');
            $table->foreignId('doctor_id')->nullable()->constrained('doctors');
            // $table->foreignId('accounting_profile_id')->constrained('accounting_profiles')->onDelete('cascade');
            $table->string('name');
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_items');
    }
};
