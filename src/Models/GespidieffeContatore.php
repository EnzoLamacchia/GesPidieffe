<?php

namespace Elamacchia\Gespidieffe\Models;

use Illuminate\Database\Eloquent\Model;

class GespidieffeContatore extends Model
{
    protected $table = 'gespidieffe_contatori';

    protected $fillable = [
        'servizio',
        'contatore_giornaliero',
        'contatore_settimanale',
        'contatore_globale',
        'data_giorno',
        'data_settimana',
    ];

    protected $casts = [
        'data_giorno'     => 'date',
        'data_settimana'  => 'date',
    ];

    public const SERVIZI = [
        'censura',
        'merge',
        'split',
        'organizza',
        'ruota',
        'numera',
    ];
}
