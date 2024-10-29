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
        Schema::create('employee_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('has_role_id')->constrained('has_roles')->onDelete('cascade');
            $table->integer("salary");
            $table->integer("rate")->default(0);
            $table->integer("target")->default(0);
            $table->integer("coa_id")->default(0);
            $table->integer("rate_type")->nullable();
            $table->integer("doctor_id")->default(0);
            $table->string("note")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employe_settings');
    }
};
