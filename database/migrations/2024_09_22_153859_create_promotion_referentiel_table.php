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
            $table->unsignedBigInteger('referentiel_id'); // Change this to unsignedBigInteger
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('referentiel_id')->references('id')->on('referentiels')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('promotion_referentiel');
    }
}
