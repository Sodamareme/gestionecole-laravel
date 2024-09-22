<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionReferentielTable extends Migration
{
    public function up()
    {
        Schema::create('promotion_referentiel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions')->onDelete('cascade');
            $table->string('referentiel_id'); // Changez le type si nécessaire
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('promotion_referentiel');
    }
}

