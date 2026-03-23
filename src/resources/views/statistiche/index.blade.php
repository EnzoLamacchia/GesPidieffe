<x-gespidieffe::layouts.app>
    <x-slot name="breadcrumb">Statistiche utilizzo</x-slot>

    @php
        // Mappa colori e icone per ciascun servizio
        $config = [
            'censura'          => ['label' => 'Censura PDF',        'icon' => '🔴', 'bg' => 'bg-red-50',    'border' => 'border-red-200',    'text' => 'text-red-700',    'badge' => 'bg-red-100 text-red-800',    'bar' => 'background:#b91c1c'],
            'merge'            => ['label' => 'Merge PDF',           'icon' => '🔵', 'bg' => 'bg-blue-50',   'border' => 'border-blue-200',   'text' => 'text-blue-700',   'badge' => 'bg-blue-100 text-blue-800',   'bar' => 'background:#1d4ed8'],
            'split'            => ['label' => 'Split PDF',           'icon' => '🟣', 'bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'text' => 'text-purple-700', 'badge' => 'bg-purple-100 text-purple-800','bar' => 'background:#7e22ce'],
            'organizza'        => ['label' => 'Organizza pagine',    'icon' => '🟡', 'bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-700', 'badge' => 'bg-yellow-100 text-yellow-800','bar' => 'background:#a16207'],
            'ruota'            => ['label' => 'Ruota pagine',        'icon' => '🟢', 'bg' => 'bg-green-50',  'border' => 'border-green-200',  'text' => 'text-green-700',  'badge' => 'bg-green-100 text-green-800',  'bar' => 'background:#15803d'],
            'numera'           => ['label' => 'Numera pagine',       'icon' => '🔷', 'bg' => 'bg-indigo-50', 'border' => 'border-indigo-200', 'text' => 'text-indigo-700', 'badge' => 'bg-indigo-100 text-indigo-800','bar' => 'background:#3730a3'],
            'unisci_organizza' => ['label' => 'Unisci e Sistema',  'icon' => '🟠', 'bg' => '',             'border' => '',                  'text' => '',                'badge' => '',                             'bar' => 'background:#ea580c',
                                   'bgStyle' => 'background:#fff7ed', 'borderStyle' => 'border:1px solid #fed7aa', 'textStyle' => 'color:#c2410c', 'badgeStyle' => 'background:#fed7aa;color:#9a3412'],
        ];
    @endphp

    <div class="max-w-6xl mx-auto px-4 py-8 space-y-10">

        {{-- ── Titolo ──────────────────────────────────────────────────────── --}}
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gray-800 flex items-center justify-center text-white text-xl shadow">📊</div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Statistiche GesPidieffe</h1>
                <p class="text-sm text-gray-500 mt-0.5">Contatori aggiornati in tempo reale · Azzeramento giornaliero alle 00:00 · Settimanale ogni lunedì</p>
            </div>
        </div>

        {{-- ── Riquadri totali aggregati ──────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

            {{-- Oggi --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-5">
                <div class="w-14 h-14 rounded-xl bg-orange-100 flex items-center justify-center text-3xl flex-shrink-0">☀️</div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Totale oggi</p>
                    <p class="text-4xl font-extrabold text-gray-800 mt-1">{{ number_format($totaleGiornaliero) }}</p>
                    <p class="text-xs text-gray-400 mt-1">elaborazioni · tutti i servizi</p>
                </div>
            </div>

            {{-- Settimana corrente --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-5">
                <div class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center text-3xl flex-shrink-0">📅</div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Settimana corrente</p>
                    <p class="text-4xl font-extrabold text-gray-800 mt-1">{{ number_format($totaleSettimanale) }}</p>
                    <p class="text-xs text-gray-400 mt-1">elaborazioni · tutti i servizi</p>
                </div>
            </div>

            {{-- Globale --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-5">
                <div class="w-14 h-14 rounded-xl bg-emerald-100 flex items-center justify-center text-3xl flex-shrink-0">🌐</div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Totale storico</p>
                    <p class="text-4xl font-extrabold text-gray-800 mt-1">{{ number_format($totaleGlobale) }}</p>
                    <p class="text-xs text-gray-400 mt-1">elaborazioni · dall'attivazione</p>
                </div>
            </div>

        </div>

        {{-- ── Dettaglio per servizio ─────────────────────────────────────── --}}
        <div>
            <h2 class="text-base font-semibold text-gray-600 mb-4 uppercase tracking-wider">Dettaglio per funzione</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($config as $servizio => $cfg)
                    @php
                        $c         = $contatori->get($servizio);
                        $useInline = isset($cfg['bgStyle']);
                    @endphp
                    <div class="rounded-2xl p-5 space-y-4 {{ $useInline ? '' : $cfg['bg'].' '.$cfg['border'].' border' }}"
                         @if($useInline) style="{{ $cfg['bgStyle'] }};{{ $cfg['borderStyle'] }}" @endif>

                        {{-- Intestazione --}}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">{{ $cfg['icon'] }}</span>
                                <span class="font-semibold text-sm {{ $useInline ? '' : $cfg['text'] }}"
                                      @if($useInline) style="{{ $cfg['textStyle'] }}" @endif>
                                    {{ $cfg['label'] }}
                                </span>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $useInline ? '' : $cfg['badge'] }}"
                                  @if($useInline) style="{{ $cfg['badgeStyle'] }}" @endif>
                                tot. {{ number_format($c->contatore_globale ?? 0) }}
                            </span>
                        </div>

                        {{-- Barre mini --}}
                        @php
                            $giornaliero = $c->contatore_giornaliero ?? 0;
                            $settimanale = $c->contatore_settimanale ?? 0;
                        @endphp

                        <div class="space-y-2">
                            {{-- Giornaliero --}}
                            <div>
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>Oggi</span>
                                    <span class="font-semibold {{ $useInline ? '' : $cfg['text'] }}"
                                          @if($useInline) style="{{ $cfg['textStyle'] }}" @endif>
                                        {{ number_format($giornaliero) }}
                                    </span>
                                </div>
                                <div class="h-2 bg-white bg-opacity-60 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $useInline ? '' : 'bg-current '.$cfg['text'] }}"
                                         style="width:{{ $totaleGiornaliero > 0 ? round(($giornaliero / $totaleGiornaliero) * 100) : 0 }}%;{{ $useInline ? $cfg['bar'] : '' }}"></div>
                                </div>
                            </div>

                            {{-- Settimanale --}}
                            <div>
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>Settimana</span>
                                    <span class="font-semibold {{ $useInline ? '' : $cfg['text'] }}"
                                          @if($useInline) style="{{ $cfg['textStyle'] }}" @endif>
                                        {{ number_format($settimanale) }}
                                    </span>
                                </div>
                                <div class="h-2 bg-white bg-opacity-60 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $useInline ? '' : 'bg-current '.$cfg['text'] }}"
                                         style="width:{{ $totaleSettimanale > 0 ? round(($settimanale / $totaleSettimanale) * 100) : 0 }}%;{{ $useInline ? $cfg['bar'] : '' }}"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── Storico settimanale ────────────────────────────────────────── --}}
        @if ($storico->isNotEmpty())
        <div>
            <h2 class="text-base font-semibold text-gray-600 mb-4 uppercase tracking-wider">Storico settimanale (ultime 12 settimane)</h2>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="px-5 py-3 text-left font-semibold">Settimana</th>
                            <th class="px-5 py-3 text-left font-semibold">Periodo</th>
                            @foreach ($config as $servizio => $cfg)
                                <th class="px-3 py-3 text-center font-semibold">
                                    <span class="{{ $cfg['text'] }}">{{ $cfg['label'] }}</span>
                                </th>
                            @endforeach
                            <th class="px-5 py-3 text-center font-semibold text-gray-700">Totale</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($storico as $chiave => $righe)
                            @php
                                $prima = $righe->first();
                                $totRiga = $righe->sum('totale');
                                $datiServizio = $righe->keyBy('servizio');
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3 font-semibold text-gray-700">{{ $chiave }}</td>
                                <td class="px-5 py-3 text-gray-500 text-xs">
                                    {{ \Carbon\Carbon::parse($prima->data_inizio_settimana)->format('d/m') }}
                                    →
                                    {{ \Carbon\Carbon::parse($prima->data_fine_settimana)->format('d/m/Y') }}
                                </td>
                                @foreach ($config as $servizio => $cfg)
                                    <td class="px-3 py-3 text-center">
                                        @if (isset($datiServizio[$servizio]) && $datiServizio[$servizio]->totale > 0)
                                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $cfg['badge'] }}">
                                                {{ number_format($datiServizio[$servizio]->totale) }}
                                            </span>
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-5 py-3 text-center font-bold text-gray-800">{{ number_format($totRiga) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- ── Footer info ────────────────────────────────────────────────── --}}
        <div class="text-center text-xs text-gray-400 pb-4 space-y-1">
            <p>I contatori <strong>giornalieri</strong> si azzerano ogni giorno alle 00:00 · i contatori <strong>settimanali</strong> si azzerano ogni lunedì alle 00:00</p>
            <p>Lo storico settimanale viene salvato automaticamente prima di ogni azzeramento · il contatore <strong>globale</strong> non si azzera mai</p>
        </div>

    </div>

</x-gespidieffe::layouts.app>
