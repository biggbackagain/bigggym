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
        Schema::create('membership_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej. "Mensual", "Semanal", "Visita Diaria"
            $table->integer('duration_days'); // Ej. 30, 7, 1
            $table->decimal('price_general', 8, 2); // Precio general
            $table->decimal('price_student', 8, 2); // Precio estudiante
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_types');
    }
};
