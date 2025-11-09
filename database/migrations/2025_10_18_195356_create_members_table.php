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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->unique();
            
            // --- Columna de la foto (ya incluida) ---
            $table->string('profile_photo_path')->nullable();
            
            // --- Columna del código (corregida) ---
            $table->string('member_code')->unique()->nullable();
            
            $table->boolean('is_student')->default(false);
            $table->string('status')->default('expired');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};