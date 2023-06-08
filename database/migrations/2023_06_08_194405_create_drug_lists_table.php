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
        Schema::create('drug_lists', function (Blueprint $table) {
            $table->id();
            $table->string('drug_name');
            $table->smallInteger('eat')->nullable();
            $table->string('portion')->nullable();
            $table->string('frequency')->nullable();
            $table->string('effect')->nullable();
            $table->integer('diagnosis_id')->nullable();
            $table->integer('doctor_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_lists');
    }
};
