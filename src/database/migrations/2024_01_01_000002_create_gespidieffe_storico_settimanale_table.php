<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gespidieffe_storico_settimanale', function (Blueprint $table) {
            $table->id();
            $table->string('servizio', 30)->index();
            $table->unsignedSmallInteger('anno');
            $table->unsignedTinyInteger('settimana');  // numero settimana ISO (1-53)
            $table->date('data_inizio_settimana');
            $table->date('data_fine_settimana');
            $table->unsignedBigInteger('totale');
            $table->timestamps();

            $table->index(['anno', 'settimana']);
            $table->index(['servizio', 'anno', 'settimana']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gespidieffe_storico_settimanale');
    }
};
