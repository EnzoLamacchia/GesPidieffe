<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gespidieffe_contatori', function (Blueprint $table) {
            $table->id();
            $table->string('servizio', 30)->index(); // censura, merge, split, organizza, ruota, numera
            $table->unsignedBigInteger('contatore_giornaliero')->default(0);
            $table->unsignedBigInteger('contatore_settimanale')->default(0);
            $table->unsignedBigInteger('contatore_globale')->default(0);
            $table->date('data_giorno')->index();       // data dell'ultimo azzeramento giornaliero
            $table->date('data_settimana')->index();    // data dell'inizio dell'ultima settimana
            $table->timestamps();

            $table->unique('servizio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gespidieffe_contatori');
    }
};
