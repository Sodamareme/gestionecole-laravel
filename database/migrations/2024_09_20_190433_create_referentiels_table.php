<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferentielsTable extends Migration
{
    public function up()
    {
        Schema::create('referentiels', function (Blueprint $table) {
            $table->id(); // This will create an auto-incrementing big integer id
            $table->string('code')->unique();
            $table->string('libelle')->unique();
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->string('statut')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('referentiels');
    }
}
