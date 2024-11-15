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
        Schema::table('has_roles', function (Blueprint $table) {
            $table->foreignId('expense_coa_id')->nullable()->constrained('c_o_a_s')->onDelete('set null');
            $table->foreignId('revenue_coa_id')->nullable()->constrained('c_o_a_s')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('has_roles', function (Blueprint $table) {
            //
        });
    }
};
