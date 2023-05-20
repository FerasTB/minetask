<?php

use App\Enums\ToothType;
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
        Schema::create('teeth', function (Blueprint $table) {
            $table->id();
            $table->integer('number_of_tooth');
            $table->integer('type')->default(ToothType::OptionTwo);
            $table->foreignId('operation_id')->nullable()->constrained('operations')->onDelete('cascade');
            $table->foreignId('diagnosis_id')->nullable()->constrained('diagnoses')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teeth');
    }
};
