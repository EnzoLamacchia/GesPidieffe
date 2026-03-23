<?php

namespace Elamacchia\Gespidieffe\Http\Controllers;

use App\Http\Controllers\Controller;
use Elamacchia\Gespidieffe\Models\GespidieffeContatore;
use Elamacchia\Gespidieffe\Models\GespidieffeStoricoSettimanale;

class StatisticheController extends Controller
{
    public function index()
    {
        // Contatori attivi (giornaliero, settimanale, globale per servizio)
        $contatori = GespidieffeContatore::orderBy('servizio')->get()->keyBy('servizio');

        // Garantisce che tutti i 6 servizi siano presenti (anche se mai usati)
        $serviziDefault = collect(GespidieffeContatore::SERVIZI)->mapWithKeys(fn($s) => [
            $s => (object) [
                'servizio'              => $s,
                'contatore_giornaliero' => 0,
                'contatore_settimanale' => 0,
                'contatore_globale'     => 0,
            ],
        ]);
        $contatori = $serviziDefault->merge($contatori);

        // Totali aggregati
        $totaleGiornaliero  = $contatori->sum('contatore_giornaliero');
        $totaleSettimanale  = $contatori->sum('contatore_settimanale');
        $totaleGlobale      = $contatori->sum('contatore_globale');

        // Storico settimanale: ultime 12 settimane, raggruppato per settimana
        $storico = GespidieffeStoricoSettimanale::orderByDesc('anno')
            ->orderByDesc('settimana')
            ->limit(12 * 7)   // max 12 settimane × 7 servizi
            ->get()
            ->groupBy(fn($r) => $r->anno . '-W' . str_pad($r->settimana, 2, '0', STR_PAD_LEFT));

        return view('gespidieffe::statistiche.index', compact(
            'contatori',
            'totaleGiornaliero',
            'totaleSettimanale',
            'totaleGlobale',
            'storico'
        ));
    }
}
