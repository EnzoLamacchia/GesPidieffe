<x-gespidieffe::layouts.app breadcrumb="Split PDF">

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
@endpush

{{-- =========================================================
     GESPIDIEFFE – Split PDF  |  Step 2: Editor
     =========================================================

     Layout:
     ┌─────────────────────────────────────────────────────┐
     │  TOOLBAR (titolo + dividi e scarica + nuovo split)   │
     ├───────────────────────┬─────────────────────────────┤
     │  SINISTRA             │  CENTRO/DESTRA               │
     │  • selezione modalità │  • modalità singole:         │
     │  • form aggiungi      │    messaggio informativo      │
     │    intervallo         │  • modalità intervalli:       │
     │  • lista intervalli   │    griglia card con miniature │
     │    salvati            │    prima/ultima pagina        │
     └───────────────────────┴─────────────────────────────┘
     ========================================================= --}}

<div class="flex flex-col w-full h-screen overflow-hidden bg-gray-100"
     x-data="splitEditor()"
     x-init="init()">

    {{-- ── TOOLBAR ──────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 px-5 py-3 bg-white border-b border-gray-200 shadow-sm flex-shrink-0 flex-wrap">

        <div class="flex items-center gap-2 mr-4">
            <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25
                         3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
            </svg>
            <span class="font-bold text-gray-800 text-sm">Split PDF</span>
        </div>

        <span class="text-xs text-gray-500 whitespace-nowrap truncate max-w-xs" x-text="original"></span>
        <span class="text-xs text-gray-400 whitespace-nowrap">
            &bull; <span x-text="totalPages"></span> pagine
        </span>

        <div class="h-6 w-px bg-gray-300 mx-1 ml-auto"></div>

        <button @click="eseguiSplit()"
                :disabled="saving || !isValid"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                       bg-purple-600 hover:bg-purple-700 text-white
                       disabled:bg-gray-300 disabled:cursor-not-allowed
                       transition-colors shadow-sm">
            <svg x-show="!saving" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021
                         18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993
                         0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0
                         0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            <span x-text="saving ? 'Elaborazione…' : 'Dividi e scarica'"></span>
        </button>

        <button @click="nuovoSplit()"
                class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium
                       border border-gray-300 bg-white text-gray-500 hover:bg-gray-50 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nuovo split
        </button>
    </div>

    {{-- ── BODY ─────────────────────────────────────────────── --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- ── Pannello sinistra ───────────────────────────────── --}}
        <div class="w-80 flex-shrink-0 bg-white border-r border-gray-200 overflow-y-auto p-5 flex flex-col gap-5">

            {{-- Modalità --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Modalità</p>

                <label class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all mb-2"
                       :class="modalita === 'singole' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="modalita" value="singole" x-model="modalita" class="mt-0.5 accent-purple-600">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Una pagina per file</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Genera <span class="font-medium" x-text="totalPages"></span> file separati, uno per ogni pagina.
                        </p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all"
                       :class="modalita === 'intervalli' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="modalita" value="intervalli" x-model="modalita" class="mt-0.5 accent-purple-600">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Intervalli personalizzati</p>
                        <p class="text-xs text-gray-500 mt-0.5">Definisci i range di pagine da estrarre.</p>
                    </div>
                </label>
            </div>

            {{-- Form aggiungi intervallo (solo modalità intervalli) --}}
            <div x-show="modalita === 'intervalli'"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0">

                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Aggiungi intervallo</p>

                <div class="bg-gray-50 rounded-xl p-4 flex flex-col gap-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Prima pagina</label>
                            <input type="number"
                                   x-model.number="inputDa"
                                   @keydown.enter.prevent="aggiungiIntervallo()"
                                   :min="1" :max="totalPages"
                                   placeholder="es. 1"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2
                                          focus:outline-none focus:ring-2 focus:ring-purple-300
                                          [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Ultima pagina</label>
                            <input type="number"
                                   x-model.number="inputA"
                                   @keydown.enter.prevent="aggiungiIntervallo()"
                                   :min="1" :max="totalPages"
                                   placeholder="es. 3"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2
                                          focus:outline-none focus:ring-2 focus:ring-purple-300
                                          [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                        </div>
                    </div>

                    {{-- Messaggio errore input --}}
                    <p x-show="inputError" x-text="inputError"
                       class="text-xs text-red-500 -mt-1"></p>

                    <button @click="aggiungiIntervallo()"
                            class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg
                                   bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold
                                   transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Aggiungi intervallo
                    </button>
                </div>

                {{-- Lista intervalli salvati --}}
                <div x-show="ranges.length > 0" class="mt-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        Intervalli definiti
                        <span class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full
                                     bg-purple-100 text-purple-700 text-xs font-bold"
                              x-text="ranges.length"></span>
                    </p>

                    <ul class="space-y-2">
                        <template x-for="(r, i) in ranges" :key="r.id">
                            <li class="flex items-center gap-2 bg-purple-50 border border-purple-200
                                       rounded-lg px-3 py-2 text-sm">
                                <span class="w-5 h-5 flex items-center justify-center rounded-full
                                             bg-purple-600 text-white text-xs font-bold flex-shrink-0"
                                      x-text="i + 1"></span>
                                <span class="flex-1 text-gray-700 font-medium"
                                      x-text="r.da === r.a ? 'Pagina ' + r.da : 'Pagine ' + r.da + ' \u2013 ' + r.a">
                                </span>
                                <span class="text-xs text-gray-400"
                                      x-text="r.da === r.a ? '1 pag.' : (r.a - r.da + 1) + ' pag.'"></span>
                                <button @click="chiediElimina(i)"
                                        class="flex-shrink-0 text-gray-400 hover:text-red-500 transition-colors ml-1"
                                        title="Rimuovi intervallo">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </li>
                        </template>
                    </ul>

                    {{-- Riepilogo --}}
                    <div class="mt-3 bg-gray-50 rounded-xl p-3 text-xs text-gray-500">
                        Verranno generati
                        <span class="font-bold text-purple-700" x-text="ranges.length"></span>
                        file PDF.
                        <span x-show="ranges.length > 1"> Scaricati come archivio ZIP.</span>
                        <span x-show="ranges.length === 1"> Scaricato direttamente come PDF.</span>
                    </div>
                </div>

                {{-- Placeholder lista vuota --}}
                <div x-show="ranges.length === 0"
                     class="mt-4 text-center text-xs text-gray-400 py-4">
                    Nessun intervallo aggiunto.<br>Usa il form qui sopra.
                </div>
            </div>

            {{-- Riepilogo modalità singole --}}
            <div x-show="modalita === 'singole'" class="bg-gray-50 rounded-xl p-4 text-sm">
                <p class="text-gray-600">
                    Verranno generati
                    <span class="font-bold text-purple-700" x-text="totalPages"></span>
                    file PDF.
                </p>
                <p x-show="totalPages > 1" class="text-xs text-gray-400 mt-1">Scaricati come archivio ZIP.</p>
                <p x-show="totalPages === 1" class="text-xs text-gray-400 mt-1">Scaricato direttamente come PDF.</p>
            </div>

        </div>

        {{-- ── Area centrale: miniature intervalli ─────────────── --}}
        <div class="flex-1 overflow-auto p-6 bg-gray-100">

            {{-- Modalità singole: messaggio --}}
            <div x-show="modalita === 'singole'"
                 class="flex flex-col items-center justify-center h-full text-center text-gray-400 gap-4">
                <svg class="w-16 h-16 text-purple-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25
                             3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                </svg>
                <div>
                    <p class="text-sm font-semibold text-gray-500">Ogni pagina diventerà un file separato</p>
                    <p class="text-xs text-gray-400 mt-1">
                        Il documento verrà diviso in <span class="font-medium" x-text="totalPages"></span> file PDF.
                    </p>
                </div>
                <p class="text-xs text-gray-400">Premi <span class="font-semibold text-purple-600">Dividi e scarica</span> per procedere.</p>
            </div>

            {{-- Modalità intervalli: griglia card --}}
            <div x-show="modalita === 'intervalli'">

                {{-- Placeholder: nessun intervallo --}}
                <div x-show="ranges.length === 0"
                     class="flex flex-col items-center justify-center h-64 text-center text-gray-400 gap-3">
                    <svg class="w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5
                                 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12
                                 M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125
                                 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <p class="text-sm text-gray-400">Aggiungi almeno un intervallo dal pannello a sinistra<br>per vedere l'anteprima delle pagine.</p>
                </div>

                {{-- Griglia card per ogni intervallo --}}
                <div x-show="ranges.length > 0"
                     class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 max-w-6xl mx-auto">

                    <template x-for="(r, i) in ranges" :key="r.id">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden
                                    hover:shadow-md transition-shadow duration-150"
                             x-init="$nextTick(() => renderCard($el, r))">

                            {{-- Header card --}}
                            <div class="flex items-center justify-between px-4 py-3 bg-purple-50 border-b border-purple-100">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 flex items-center justify-center rounded-full
                                                 bg-purple-600 text-white text-xs font-bold"
                                          x-text="i + 1"></span>
                                    <span class="text-sm font-semibold text-gray-800"
                                          x-text="r.da === r.a ? 'Pagina ' + r.da : 'Pagine ' + r.da + ' \u2013 ' + r.a"></span>
                                </div>
                                <span class="text-xs text-gray-400"
                                      x-text="r.da === r.a ? '1 pag.' : (r.a - r.da + 1) + ' pag.'"></span>
                            </div>

                            {{-- Miniature prima / ultima pagina --}}
                            <div class="flex gap-2 p-3">

                                {{-- Miniatura prima pagina --}}
                                <div class="flex-1 flex flex-col items-center gap-1">
                                    <span class="text-xs text-gray-400">Pag. <span x-text="r.da"></span></span>
                                    <div class="relative w-full bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center"
                                         style="min-height: 120px;">
                                        <canvas data-thumb="first"
                                                class="max-w-full max-h-full block object-contain"></canvas>
                                        <div data-spinner="first"
                                             class="absolute inset-0 flex items-center justify-center bg-gray-100">
                                            <svg class="w-5 h-5 animate-spin text-purple-300" fill="none" viewBox="0 0 24 24"
                                                 stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993
                                                         0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0
                                                         0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                {{-- Separatore + miniatura ultima pagina (sempre presenti nel DOM, visibili solo se da !== a) --}}
                                <template x-if="r.da !== r.a">
                                    <div class="flex gap-2 flex-1">
                                        {{-- Separatore --}}
                                        <div class="flex flex-col items-center justify-center px-1">
                                            <svg class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24"
                                                 stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                                            </svg>
                                        </div>
                                        {{-- Miniatura ultima pagina --}}
                                        <div class="flex-1 flex flex-col items-center gap-1">
                                            <span class="text-xs text-gray-400">Pag. <span x-text="r.a"></span></span>
                                            <div class="relative w-full bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center"
                                                 style="min-height: 120px;">
                                                <canvas data-thumb="last"
                                                        class="max-w-full max-h-full block object-contain"></canvas>
                                                <div data-spinner="last"
                                                     class="absolute inset-0 flex items-center justify-center bg-gray-100">
                                                    <svg class="w-5 h-5 animate-spin text-purple-300" fill="none" viewBox="0 0 24 24"
                                                         stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993
                                                                 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0
                                                                 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Footer card: pulsante rimuovi --}}
                            <div class="px-4 pb-3">
                                <button @click="chiediElimina(i)"
                                        class="w-full flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg
                                               text-xs font-medium text-red-500 border border-red-200
                                               hover:bg-red-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Rimuovi
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Dialog conferma eliminazione ────────────────────── --}}
    <div x-show="confirmDialog.show"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
         @click.self="confirmDialog.show = false">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-start gap-3 mb-4">
                <div class="flex-shrink-0 w-9 h-9 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73
                                 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898
                                 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 text-sm">Rimuovi intervallo</p>
                    <p class="text-sm text-gray-500 mt-1" x-text="confirmDialog.msg"></p>
                </div>
            </div>
            <div class="flex gap-3 justify-end">
                <button @click="confirmDialog.show = false"
                        class="px-4 py-2 rounded-lg text-sm font-medium border border-gray-300
                               bg-white text-gray-600 hover:bg-gray-50 transition-colors">
                    Annulla
                </button>
                <button @click="confermaElimina()"
                        class="px-4 py-2 rounded-lg text-sm font-semibold bg-red-600
                               hover:bg-red-700 text-white transition-colors">
                    Rimuovi
                </button>
            </div>
        </div>
    </div>

    {{-- ── Toast notifiche ─────────────────────────────────── --}}
    <div x-show="toast.show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         :class="toast.type === 'error' ? 'bg-red-600' : 'bg-green-600'"
         class="fixed bottom-6 right-6 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium z-50">
        <span x-text="toast.msg"></span>
    </div>
</div>

{{-- ── Dati PHP → JS ─────────────────────────────────────── --}}
<script>
    window._splitEditor = {
        file:       '{{ $file }}',
        totalPages: {{ $pages }},
        original:   @json($original),
        routes: {
            applica:     '{{ route('gespidieffe.split.applica') }}',
            download:    '{{ url('gespidieffe/split/download') }}',
            downloadZip: '{{ url('gespidieffe/split/download-zip') }}',
            elimina:     '{{ route('gespidieffe.split.elimina', ['file' => $file]) }}',
            pdf:         '{{ route('gespidieffe.split.pdf', ['file' => $file]) }}',
        },
        csrf: '{{ csrf_token() }}',
    };
</script>

{{-- ── Script Alpine component ────────────────────────────── --}}
<script>
// pdfDoc salvato FUORI dal Proxy di Alpine per evitare che i membri
// privati di PDF.js vengano wrappati e diventino inaccessibili
window._splitPdfDoc = null;

function splitEditor() {
    return {
        file:       window._splitEditor.file,
        totalPages: window._splitEditor.totalPages,
        original:   window._splitEditor.original,

        // Modalità
        modalita: 'singole',

        // Form aggiungi intervallo
        inputDa:    null,
        inputA:     null,
        inputError: '',

        // Lista intervalli salvati: [{id, da, a}]
        ranges: [],
        nextId: 1,

        // Dialog conferma eliminazione
        confirmDialog: { show: false, msg: '', idx: null },

        saving: false,
        toast:  { show: false, msg: '', type: 'success' },

        get isValid() {
            if (this.modalita === 'singole') return this.totalPages > 0;
            return this.ranges.length > 0;
        },

        // ── Init: carica PDF.js ──────────────────────────────────
        async init() {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
            try {
                window._splitPdfDoc = await pdfjsLib.getDocument(window._splitEditor.routes.pdf).promise;
            } catch (e) {
                console.error('PDF.js: impossibile caricare il documento', e);
            }
        },

        // ── Aspetta che pdfDoc sia pronto (max 10s) ─────────────
        waitForPdfDoc(timeoutMs = 10000) {
            return new Promise((resolve) => {
                if (window._splitPdfDoc) { resolve(window._splitPdfDoc); return; }
                const interval = setInterval(() => {
                    if (window._splitPdfDoc) { clearInterval(interval); resolve(window._splitPdfDoc); }
                }, 100);
                setTimeout(() => { clearInterval(interval); resolve(null); }, timeoutMs);
            });
        },

        // ── Renderizza le miniature di una card appena inserita nel DOM ──
        // cardEl = $el della card (già nel DOM grazie a x-init + $nextTick)
        async renderCard(cardEl, r) {
            await this.waitForPdfDoc();
            if (!window._splitPdfDoc) return;
            await this.renderThumbInEl(
                cardEl.querySelector('[data-thumb="first"]'),
                cardEl.querySelector('[data-spinner="first"]'),
                r.da
            );
            if (r.da !== r.a) {
                // Il secondo canvas è dentro un <template x-if>:
                // aspettiamo che Alpine lo inserisca nel DOM
                const lastCanvas  = await this.waitForDescendant(cardEl, '[data-thumb="last"]');
                const lastSpinner = cardEl.querySelector('[data-spinner="last"]');
                await this.renderThumbInEl(lastCanvas, lastSpinner, r.a);
            }
        },

        // Attende che un selettore compaia come discendente di un elemento
        waitForDescendant(parentEl, selector, timeoutMs = 3000) {
            return new Promise((resolve) => {
                const found = parentEl.querySelector(selector);
                if (found) { resolve(found); return; }
                const observer = new MutationObserver(() => {
                    const el = parentEl.querySelector(selector);
                    if (el) { observer.disconnect(); resolve(el); }
                });
                observer.observe(parentEl, { childList: true, subtree: true });
                setTimeout(() => { observer.disconnect(); resolve(null); }, timeoutMs);
            });
        },

        // Renderizza una pagina PDF su un canvas già presente nel DOM
        async renderThumbInEl(canvas, spinner, pageNum) {
            if (!canvas || !window._splitPdfDoc) return;
            try {
                const page     = await window._splitPdfDoc.getPage(pageNum);
                const viewport = page.getViewport({ scale: 0.6 });
                canvas.width   = viewport.width;
                canvas.height  = viewport.height;
                await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
                page.cleanup();
                if (spinner) spinner.style.display = 'none';
            } catch (e) {
                if (spinner) spinner.innerHTML = '<span class="text-xs text-gray-400">errore</span>';
            }
        },

        // ── Aggiungi un nuovo intervallo ─────────────────────────
        async aggiungiIntervallo() {
            this.inputError = '';

            const da = parseInt(this.inputDa);
            const a  = parseInt(this.inputA);

            if (!this.inputDa && this.inputDa !== 0) {
                this.inputError = 'Inserisci la prima pagina.'; return;
            }
            if (!this.inputA && this.inputA !== 0) {
                this.inputError = 'Inserisci l\'ultima pagina.'; return;
            }
            if (isNaN(da) || da < 1) {
                this.inputError = 'La prima pagina deve essere ≥ 1.'; return;
            }
            if (isNaN(a) || a < da) {
                this.inputError = 'L\'ultima pagina deve essere ≥ della prima.'; return;
            }
            if (a > this.totalPages) {
                this.inputError = `L'ultima pagina non può superare ${this.totalPages}.`; return;
            }

            const id = this.nextId++;
            this.ranges.push({ id, da, a });

            // Reset form
            this.inputDa = null;
            this.inputA  = null;

        },

        // ── Richiedi conferma eliminazione ───────────────────────
        chiediElimina(idx) {
            const r   = this.ranges[idx];
            const lab = r.da === r.a ? `pagina ${r.da}` : `pagine ${r.da}–${r.a}`;
            this.confirmDialog = {
                show: true,
                msg:  `Sei sicuro di voler rimuovere l'intervallo "${lab}"?`,
                idx,
            };
        },

        // ── Conferma ed esegui eliminazione ─────────────────────
        confermaElimina() {
            const idx = this.confirmDialog.idx;
            if (idx !== null && idx >= 0 && idx < this.ranges.length) {
                this.ranges.splice(idx, 1);
            }
            this.confirmDialog = { show: false, msg: '', idx: null };
        },

        // ── Esegui split ─────────────────────────────────────────
        async eseguiSplit() {
            if (this.saving || !this.isValid) return;
            this.saving = true;
            try {
                // Costruisce la stringa intervalli dal array ranges
                const intervalliStr = this.modalita === 'intervalli'
                    ? this.ranges.map(r => r.da === r.a ? String(r.da) : `${r.da}-${r.a}`).join(', ')
                    : null;

                const payload = {
                    file:       this.file,
                    modalita:   this.modalita,
                    intervalli: intervalliStr,
                };

                const resp = await fetch(window._splitEditor.routes.applica, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window._splitEditor.csrf,
                    },
                    body: JSON.stringify(payload),
                });

                if (!resp.ok) {
                    const err = await resp.json().catch(() => ({}));
                    throw new Error(err.message || 'Errore durante lo split');
                }

                const data = await resp.json();

                const a    = document.createElement('a');
                a.download = data.tipo === 'zip' ? 'split.zip' : (data.filename || 'split.pdf');
                a.href     = data.tipo === 'zip'
                    ? window._splitEditor.routes.downloadZip + '/' + data.download_token
                    : window._splitEditor.routes.download    + '/' + data.download_token;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);

                this.showToast(data.tipo === 'zip' ? 'Archivio ZIP scaricato!' : 'PDF scaricato!', 'success');
            } catch (err) {
                this.showToast(err.message, 'error');
            } finally {
                this.saving = false;
            }
        },

        showToast(msg, type = 'success') {
            this.toast = { show: true, msg, type };
            setTimeout(() => { this.toast.show = false; }, 3500);
        },

        eliminaFile() {
            const url  = window._splitEditor.routes.elimina;
            const blob = new Blob(
                [JSON.stringify({ _method: 'DELETE', _token: window._splitEditor.csrf })],
                { type: 'application/json' }
            );
            navigator.sendBeacon(url, blob);
        },

        nuovoSplit() {
            this.eliminaFile();
            window.location.href = '{{ route('gespidieffe.split') }}';
        },
    };
}

window.addEventListener('beforeunload', () => {
    const s = window._splitEditor;
    if (!s) return;
    const blob = new Blob(
        [JSON.stringify({ _method: 'DELETE', _token: s.csrf })],
        { type: 'application/json' }
    );
    navigator.sendBeacon(s.routes.elimina, blob);
});
</script>

</x-gespidieffe::layouts.app>