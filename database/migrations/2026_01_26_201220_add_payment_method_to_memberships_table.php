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
        // Target exacto a la tabla subscriptions
        Schema::table('subscriptions', function (Blueprint $table) {
            // Lo agregamos después de membership_type_id que sí existe en tu tabla
            $table->string('payment_method')->nullable()->after('membership_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};