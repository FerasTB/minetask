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
        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->string('drug_name');
            $table->boolean('eat')->nullable();
            $table->string('portion')->nullable();
            $table->string('frequency')->nullable();
            $table->string('note')->nullable();
            $table->string('effect')->nullable();
            $table->foreignId('diagnosis_id')->constrained('diagnoses')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
};
