<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionsTable extends Migration
{
    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('duree'); // Durée en jours ou en mois, selon votre besoin
            $table->enum('etat', ['Actif', 'Cloturé', 'Inactif']);
            $table->string('photo')->nullable();
            $table->foreignId('referentiel_id')->constrained('referentiels')->onDelete('cascade');
            $table->string('referentiel_id')->nullable(); // Changez le type en string si nécessaire

            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('promotions');
    }
}
