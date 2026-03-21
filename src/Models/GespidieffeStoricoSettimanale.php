<?php

namespace Elamacchia\Gespidieffe\Models;

use Illuminate\Database\Eloquent\Model;

class GespidieffeStoricoSettimanale extends Model
{
    protected $table = 'gespidieffe_storico_settimanale';

    protected $fillable = [
        'servizio',
        'anno',
        'settimana',
        'data_inizio_settimana',
        'data_fine_settimana',
        'totale',
    ];

    protected $casts = [
        'data_inizio_settimana' => 'date',
        'data_fine_settimana'   => 'date',
    ];
}
