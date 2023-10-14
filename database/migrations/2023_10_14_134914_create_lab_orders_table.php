<?php

use App\Enums\LabOrderStatus;
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
        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accounting_profile_id')->constrained('accounting_profiles')->onDelete('cascade');
            $table->foreignId('patient_id')->nullable()->constrained('patients')->onDelete('cascade');
            $table->time('received_date')->nullable();
            $table->time('delivery_date')->nullable();
            $table->integer('status')->define(LabOrderStatus::Draft);
            // $table->foreignId('current_step_id')->nullable()->constrained('roles')->after('email');
            $table->integer('steps')->nullable();
            $table->string('attached_materials')->nullable();
            $table->string('note')->nullable();
            $table->string('patient_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_orders');
    }
};
