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
        Schema::create('dental_lab_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('COA_id')->constrained('c_o_a_s')->onDelete('cascade');
            $table->foreignId('dental_lab_id')->nullable()->constrained('dental_labs');
            $table->foreignId('doctor_id')->nullable()->constrained('doctors');
            $table->string('name');
            $table->integer('cost')->nullable();
            $table->string('description')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_lab_items');
    }
};
