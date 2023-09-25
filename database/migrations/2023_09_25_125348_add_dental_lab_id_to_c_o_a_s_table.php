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
        Schema::table('c_o_a_s', function (Blueprint $table) {
            $table->foreignId('dental_lab_id')->nullable()->constrained('dental_labs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('c_o_a_s', function (Blueprint $table) {
            //
        });
    }
};
