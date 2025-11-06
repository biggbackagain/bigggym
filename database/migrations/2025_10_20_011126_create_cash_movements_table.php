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
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['entry', 'exit']); // Tipo: Entrada o Salida
            $table->decimal('amount', 10, 2); // Monto positivo siempre
            $table->string('description'); // Motivo
            $table->foreignId('user_id')->constrained('users'); // Quién lo registró
            $table->timestamps(); // Fecha y hora del movimiento
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};