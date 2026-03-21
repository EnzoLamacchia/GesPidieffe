<?php

namespace Elamacchia\Gespidieffe\Services;

use Carbon\Carbon;
use Elamacchia\Gespidieffe\Models\GespidieffeContatore;

class ContatorePdfService
{
    /**
     * Incrementa i tre contatori (giornaliero, settimanale, globale) per il servizio indicato.
     * Crea il record se non esiste ancora (prima esecuzione).
     */
    public function incrementa(string $servizio): void
    {
        $oggi    = Carbon::today();
        $lunedi  = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();

        $record = GespidieffeContatore::firstOrCreate(
            ['servizio' => $servizio],
            [
                'contatore_giornaliero' => 0,
                'contatore_settimanale' => 0,
                'contatore_globale'     => 0,
                'data_giorno'           => $oggi->toDateString(),
                'data_settimana'        => $lunedi,
            ]
        );

        $record->increment('contatore_giornaliero');
        $record->increment('contatore_settimanale');
        $record->increment('contatore_globale');
    }
}
