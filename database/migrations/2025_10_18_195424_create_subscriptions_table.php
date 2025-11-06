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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            // Vincula la suscripción a un miembro
            $table->foreignId('member_id')->constrained('members');
            // Vincula al tipo de membresía que compró
            $table->foreignId('membership_type_id')->constrained('membership_types');
            // Vincula al pago que se generó
            $table->foreignId('payment_id')->constrained('payments');
            
            $table->date('start_date');
            $table->date('end_date'); // La fecha de vencimiento
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
