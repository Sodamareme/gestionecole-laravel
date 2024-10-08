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
        Schema::create('apprenants', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('referentiel_id');
            $table->string('promotion_id');
            $table->string('email')->unique();
            $table->string('photo')->nullable();
            $table->string('matricule')->unique();
            $table->string('statut')->default('Actif'); // For 'Abandon' or 'Actif' statuses
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apprenants');
    }
};
