<?php

namespace Elamacchia\Gespidieffe\Console;

use Carbon\Carbon;
use Elamacchia\Gespidieffe\Models\GespidieffeContatore;
use Elamacchia\Gespidieffe\Models\GespidieffeStoricoSettimanale;
use Illuminate\Console\Command;

class AzzeraContatoriCommand extends Command
{
    protected $signature   = 'gespidieffe:azzera-contatori';
    protected $description = 'Azzera i contatori giornalieri (ogni giorno a mezzanotte) e settimanali (ogni domenica a mezzanotte), salvando lo storico settimanale prima dell\'azzeramento.';

    public function handle(): int
    {
        $oggi   = Carbon::today();
        $ieri   = Carbon::yesterday();

        // ── 1. Azzeramento giornaliero ────────────────────────────────────
        // Azzera tutti i contatori il cui data_giorno è diverso da oggi.
        GespidieffeContatore::where('data_giorno', '<', $oggi->toDateString())
            ->update([
                'contatore_giornaliero' => 0,
                'data_giorno'           => $oggi->toDateString(),
            ]);

        // ── 2. Azzeramento settimanale (solo la domenica) ─────────────────
        // La domenica corrispondente all'ieri (= fine settimana appena conclusa)
        if ($ieri->dayOfWeek === Carbon::SUNDAY) {
            $this->salvaEAzeraSettimanale($ieri);
        }

        $this->info('Contatori aggiornati correttamente.');

        return self::SUCCESS;
    }

    private function salvaEAzeraSettimanale(Carbon $domenica): void
    {
        // La settimana che si conclude oggi (domenica): lunedì → domenica
        $lunedi   = $domenica->copy()->startOfWeek(Carbon::MONDAY);
        $anno     = (int) $lunedi->format('o');    // anno ISO
        $settimana = (int) $lunedi->format('W');   // settimana ISO

        $lunediStr   = $lunedi->toDateString();
        $domenicaStr = $domenica->toDateString();

        $contatori = GespidieffeContatore::all();

        foreach ($contatori as $record) {
            // Salva lo storico solo se il valore è > 0 (evita righe inutili)
            if ($record->contatore_settimanale > 0) {
                GespidieffeStoricoSettimanale::updateOrCreate(
                    [
                        'servizio'  => $record->servizio,
                        'anno'      => $anno,
                        'settimana' => $settimana,
                    ],
                    [
                        'data_inizio_settimana' => $lunediStr,
                        'data_fine_settimana'   => $domenicaStr,
                        'totale'                => $record->contatore_settimanale,
                    ]
                );
            }

            // Azzera il contatore settimanale
            $record->update([
                'contatore_settimanale' => 0,
                'data_settimana'        => Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString(),
            ]);
        }

        $this->info("Storico settimanale salvato per settimana {$anno}-W{$settimana}.");
    }
}
