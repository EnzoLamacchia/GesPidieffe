<x-gespidieffe::layouts.app breadcrumb="Numera pagine">

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
@endpush

{{-- =========================================================
     GESPIDIEFFE – Numera pagine  |  Step 2: Editor

     Layout:
     ┌──────────────────────────────────────────────────────────┐
     │  TOOLBAR: titolo · Salva PDF · Nuovo                     │
     ├────────────────────────┬─────────────────────────────────┤
     │  PANNELLO OPZIONI      │  ANTEPRIMA PDF.js (prima pag.)  │
     │  - Formato numero      │                                 │
     │  - Posizione (9 punti) │                                 │
     │  - Dimensione font     │                                 │
     │  - Prima pagina        │                                 │
     │  - Numero iniziale     │                                 │
     └────────────────────────┴─────────────────────────────────┘
     ========================================================= --}}

<div class="flex flex-col w-full h-screen overflow-hidden bg-gray-100"
     x-data="numeraEditor()"
     x-init="init()">

    {{-- ── TOOLBAR ──────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 px-5 py-3 bg-white border-b border-gray-200 shadow-sm flex-shrink-0 flex-wrap">

        <div class="flex items-center gap-2 mr-4">
            <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5l-3.9 19.5m-2.1-19.5l-3.9 19.5" />
            </svg>
            <span class="font-bold text-gray-800 text-sm">Numera pagine</span>
        </div>

        <span class="text-xs text-gray-500 whitespace-nowrap truncate max-w-xs" x-text="original"></span>
        <span class="text-xs text-gray-400 whitespace-nowrap">
            &bull; <span x-text="totalPages"></span> pagine
        </span>

        <div class="h-6 w-px bg-gray-300 mx-1 ml-auto"></div>

        {{-- Salva --}}
        <button @click="eseguiNumera()"
                :disabled="saving"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                       bg-indigo-600 hover:bg-indigo-700 text-white
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
            <span x-text="saving ? 'Elaborazione…' : 'Salva PDF'"></span>
        </button>

        <button @click="nuovoNumera()"
                class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium
                       border border-gray-300 bg-white text-gray-500 hover:bg-gray-50 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nuovo
        </button>
    </div>

    {{-- ── BODY ─────────────────────────────────────────────── --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- ── PANNELLO OPZIONI (sinistra) ──────────────────── --}}
        <div class="w-80 flex-shrink-0 bg-white border-r border-gray-200 overflow-y-auto p-5 flex flex-col gap-5">

            {{-- Formato numero --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Formato numero</label>
                <div class="grid grid-cols-2 gap-2">
                    <template x-for="fmt in formati" :key="fmt.value">
                        <button @click="opzioni.formato = fmt.value"
                                :class="opzioni.formato === fmt.value
                                    ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold'
                                    : 'border-gray-200 bg-gray-50 text-gray-600 hover:border-indigo-300'"
                                class="border-2 rounded-lg px-3 py-2 text-sm transition-colors text-center">
                            <span x-text="fmt.label"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Posizione (griglia 3x3) --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Posizione</label>
                <div class="flex justify-center">
                    <div class="grid grid-cols-3 gap-1.5">
                        <template x-for="pos in posizioni" :key="pos.value">
                            <button @click="opzioni.posizione = pos.value"
                                    :title="pos.label"
                                    :class="opzioni.posizione === pos.value
                                        ? 'bg-indigo-500 text-white'
                                        : 'bg-gray-100 text-gray-400 hover:bg-indigo-100 hover:text-indigo-600'"
                                    class="w-12 h-10 rounded-lg flex items-center justify-center transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16">
                                    <circle cx="8" cy="8" r="3"/>
                                </svg>
                            </button>
                        </template>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-1.5 text-center" x-text="posizioneLabel"></p>
            </div>

            {{-- Dimensione font --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                    Dimensione font — <span class="text-indigo-600 font-bold" x-text="opzioni.font_size + ' pt'"></span>
                </label>
                <input type="range" min="6" max="24" step="1"
                       x-model.number="opzioni.font_size"
                       class="w-full accent-indigo-600">
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                    <span>6 pt</span><span>24 pt</span>
                </div>
            </div>

            {{-- Prima pagina da numerare --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Prima pagina da numerare</label>
                <div class="flex items-center justify-center gap-2">
                    <button @click="opzioni.prima_pagina = Math.max(1, opzioni.prima_pagina - 1)"
                            class="w-8 h-8 rounded-lg border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                        </svg>
                    </button>
                    <input type="number" min="1" :max="totalPages"
                           x-model.number="opzioni.prima_pagina"
                           class="w-16 text-center border border-gray-300 rounded-lg py-1.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none">
                    <button @click="opzioni.prima_pagina = Math.min(totalPages, opzioni.prima_pagina + 1)"
                            class="w-8 h-8 rounded-lg border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-1">Le pagine precedenti non verranno numerate</p>
            </div>

            {{-- Numero iniziale --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Numero iniziale</label>
                <div class="flex items-center justify-center gap-2">
                    <button @click="opzioni.numero_inizio = Math.max(1, opzioni.numero_inizio - 1)"
                            class="w-8 h-8 rounded-lg border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                        </svg>
                    </button>
                    <input type="number" min="1"
                           x-model.number="opzioni.numero_inizio"
                           class="w-16 text-center border border-gray-300 rounded-lg py-1.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none">
                    <button @click="opzioni.numero_inizio++"
                            class="w-8 h-8 rounded-lg border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-1">Es. inizia da 5 per continuare una numerazione</p>
            </div>

            {{-- Riepilogo --}}
            <div class="mt-auto pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-2">Anteprima etichetta</p>
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-3 text-center">
                    <span class="text-sm font-bold text-indigo-700" x-text="labelAnteprima"></span>
                </div>
            </div>
        </div>

        {{-- ── ANTEPRIMA (destra) ───────────────────────────── --}}
        <div class="flex-1 overflow-auto p-6 flex flex-col items-center">

            {{-- Caricamento --}}
            <div x-show="loading"
                 class="flex flex-col items-center justify-center h-full gap-4 text-gray-400">
                <svg class="w-10 h-10 animate-spin text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993
                             0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0
                             0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <p class="text-sm">Caricamento anteprima…</p>
            </div>

            {{-- Anteprima prima pagina --}}
            <div x-show="!loading" class="w-full max-w-2xl">
                <p class="text-xs text-gray-400 text-center mb-3">Anteprima prima pagina del documento</p>

                <div class="relative bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
                    <canvas id="previewCanvas" class="block w-full"></canvas>

                    {{-- Overlay numero anteprima --}}
                    <div id="numeroOverlay"
                         class="absolute pointer-events-none select-none font-bold text-gray-800"
                         :style="overlayStyle"
                         x-text="labelAnteprima">
                    </div>
                </div>

                <p class="text-xs text-gray-400 text-center mt-3">
                    L'anteprima è indicativa — posizione e dimensione reali dipendono dalle dimensioni del PDF
                </p>
            </div>
        </div>
    </div>

    {{-- ── Toast ────────────────────────────────────────────── --}}
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
    window._numeraEditor = {
        file:       '{{ $file }}',
        totalPages: {{ $pages }},
        original:   @json($original),
        routes: {
            applica:  '{{ route('gespidieffe.numera.applica') }}',
            download: '{{ url('gespidieffe/numera/download') }}',
            elimina:  '{{ route('gespidieffe.numera.elimina', ['file' => $file]) }}',
            pdf:      '{{ route('gespidieffe.numera.pdf', ['file' => $file]) }}',
        },
        csrf: '{{ csrf_token() }}',
    };
</script>

<script>
window._numeraPdfDoc = null;

function numeraEditor() {
    return {
        file:       window._numeraEditor.file,
        totalPages: window._numeraEditor.totalPages,
        original:   window._numeraEditor.original,

        loading: true,
        saving:  false,
        toast:   { show: false, msg: '', type: 'success' },

        // Opzioni di numerazione
        opzioni: {
            formato:       'numero',
            posizione:     'bc',
            font_size:     10,
            prima_pagina:  1,
            numero_inizio: 1,
        },

        formati: [
            { value: 'numero',   label: '1, 2, 3…' },
            { value: 'pagina',   label: 'Pagina 1…' },
            { value: 'totale',   label: '1 / N…' },
            { value: 'trattini', label: '- 1 -…' },
        ],

        posizioni: [
            { value: 'tl', label: 'Alto sinistra' },
            { value: 'tc', label: 'Alto centro' },
            { value: 'tr', label: 'Alto destra' },
            { value: 'ml', label: 'Centro sinistra' },
            { value: 'mc', label: 'Centro' },
            { value: 'mr', label: 'Centro destra' },
            { value: 'bl', label: 'Basso sinistra' },
            { value: 'bc', label: 'Basso centro' },
            { value: 'br', label: 'Basso destra' },
        ],

        // ── Computed ──────────────────────────────────────────
        get labelAnteprima() {
            const n     = this.opzioni.numero_inizio;
            const total = this.totalPages - this.opzioni.prima_pagina + this.opzioni.numero_inizio;
            switch (this.opzioni.formato) {
                case 'numero':   return String(n);
                case 'pagina':   return 'Pagina ' + n;
                case 'totale':   return n + ' / ' + total;
                case 'trattini': return '- ' + n + ' -';
                default:         return String(n);
            }
        },

        get posizioneLabel() {
            return (this.posizioni.find(p => p.value === this.opzioni.posizione) || {}).label || '';
        },

        get overlayStyle() {
            const canvas = document.getElementById('previewCanvas');
            if (!canvas || !canvas.width) return 'display:none';

            const rect   = canvas.getBoundingClientRect();
            const W      = canvas.offsetWidth;
            const H      = canvas.offsetHeight;
            const fs     = this.opzioni.font_size;
            const margin = Math.round(W * 0.03);
            const pos    = this.opzioni.posizione;

            // Font size scalato alla larghezza canvas (proxy)
            const scaledFs = Math.round(fs * (W / 595));

            let top, left, transform = '';

            if (pos.startsWith('t'))      top  = margin + 'px';
            else if (pos.startsWith('b')) top  = (H - margin - scaledFs * 1.4) + 'px';
            else                          top  = ((H - scaledFs * 1.4) / 2) + 'px';

            if (pos.endsWith('l'))      { left = margin + 'px'; }
            else if (pos.endsWith('r')) { left = (W - margin) + 'px'; transform = 'translateX(-100%)'; }
            else                        { left = '50%'; transform = 'translateX(-50%)'; }

            return `top:${top}; left:${left}; transform:${transform}; font-size:${scaledFs}px; white-space:nowrap;`;
        },

        // ── Init ─────────────────────────────────────────────
        async init() {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

            try {
                window._numeraPdfDoc = await pdfjsLib.getDocument(
                    window._numeraEditor.routes.pdf
                ).promise;
                await this.renderPreview();
            } catch (e) {
                console.error('PDF.js: impossibile caricare il documento', e);
            }

            this.loading = false;
        },

        // ── Render anteprima prima pagina ─────────────────────
        async renderPreview() {
            if (!window._numeraPdfDoc) return;
            const canvas  = document.getElementById('previewCanvas');
            if (!canvas) return;

            const pdfPage  = await window._numeraPdfDoc.getPage(1);
            const viewport = pdfPage.getViewport({ scale: 1.5 });
            canvas.width   = viewport.width;
            canvas.height  = viewport.height;
            await pdfPage.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
            pdfPage.cleanup();
        },

        // ── Esegui e scarica ─────────────────────────────────
        async eseguiNumera() {
            if (this.saving) return;
            this.saving = true;
            try {
                const resp = await fetch(window._numeraEditor.routes.applica, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window._numeraEditor.csrf,
                    },
                    body: JSON.stringify({
                        file:          this.file,
                        formato:       this.opzioni.formato,
                        posizione:     this.opzioni.posizione,
                        font_size:     this.opzioni.font_size,
                        prima_pagina:  this.opzioni.prima_pagina,
                        numero_inizio: this.opzioni.numero_inizio,
                    }),
                });

                if (!resp.ok) {
                    const err = await resp.json().catch(() => ({}));
                    throw new Error(err.message || 'Errore durante l\'elaborazione');
                }

                const data = await resp.json();

                const a    = document.createElement('a');
                a.download = 'numerato.pdf';
                a.href     = window._numeraEditor.routes.download + '/' + data.download_token;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);

                this.showToast('PDF scaricato!', 'success');
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
            const url  = window._numeraEditor.routes.elimina;
            const blob = new Blob(
                [JSON.stringify({ _method: 'DELETE', _token: window._numeraEditor.csrf })],
                { type: 'application/json' }
            );
            navigator.sendBeacon(url, blob);
        },

        nuovoNumera() {
            this.eliminaFile();
            window.location.href = '{{ route('gespidieffe.numera') }}';
        },
    };
}

window.addEventListener('beforeunload', () => {
    const s = window._numeraEditor;
    if (!s) return;
    const blob = new Blob(
        [JSON.stringify({ _method: 'DELETE', _token: s.csrf })],
        { type: 'application/json' }
    );
    navigator.sendBeacon(s.routes.elimina, blob);
});
</script>

</x-gespidieffe::layouts.app>
