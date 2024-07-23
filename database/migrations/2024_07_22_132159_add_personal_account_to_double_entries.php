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
        Schema::table('double_entries', function (Blueprint $table) {
            $table->foreignId('accounting_profile_id')->nullable()->constrained('accounting_profiles')->onDelete('set null');
            $table->integer('running_balance')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('double_entries', function (Blueprint $table) {
            //
        });
    }
};
