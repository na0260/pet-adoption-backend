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
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('breed')->nullable();
            $table->string('age')->nullable();
            $table->string('color')->nullable();
            $table->string('gender')->nullable();
            $table->text('description')->nullable();
            $table->string('health_condition')->nullable();
            $table->string('adoption_fee')->nullable();
            $table->enum('availability', ['available', 'adopted'])->default('available');
            $table->foreignId('shelter_id')->constrained('shelters')->onUpdate('cascade')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
