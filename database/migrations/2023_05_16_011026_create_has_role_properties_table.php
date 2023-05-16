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
        Schema::create('has_role_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('has_role_id')->constrained('has_roles')->onDelete('cascade');
            $table->integer("type");
            $table->boolean("read")->default(false);
            $table->boolean("write")->default(false);
            $table->boolean("edit")->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('has_role_properties');
    }
};
