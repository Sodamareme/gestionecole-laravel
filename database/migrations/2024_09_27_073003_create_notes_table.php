<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {    
   
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('apprenant_id'); // Clé étrangère
            $table->decimal('note', 5, 2);
            $table->timestamps();
        
            $table->foreign('apprenant_id')->references('id')->on('apprenants')->onDelete('cascade');
        });
        
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
