<?php

use App\Enums\AnesthesiaType;
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
        Schema::create('teeth_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patientCase_id')->constrained('medical_cases')->onDelete('cascade');
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments');
            $table->longText('description')->nullable();
            $table->smallInteger('anesthesia_type')->default(AnesthesiaType::None);
            $table->text('after_treatment_instruction')->nullable();
            $table->integer('number_of_teeth')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teeth_records');
    }
};
