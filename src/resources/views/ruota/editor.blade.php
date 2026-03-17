<x-gespidieffe::layouts.app breadcrumb="Ruota pagine">

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
@endpush

{{-- =========================================================
     GESPIDIEFFE – Ruota pagine  |  Step 2: Editor

     Layout:
     ┌──────────────────────────────────────────────────────────┐
     │  TOOLBAR: titolo · Ruota tutto · Ripristina · Salva PDF  │
     ├──────────────────────────────────────────────────────────┤
     │  GRIGLIA MINIATURE                                        │
     │  Ogni card: miniatura ruotata CSS + badge gradi + click  │
     └──────────────────────────────────────────────────────────┘
     ========================================================= --}}

<div class="flex flex-col w-full h-screen overflow-hidden bg-gray-100"
     x-data="ruotaEditor()"
     x-init="init()">

    {{-- ── TOOLBAR ──────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 px-5 py-3 bg-white border-b border-gray-200 shadow-sm flex-shrink-0 flex-wrap">

        <div class="flex items-center gap-2 mr-4">
            <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181
                         3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181
                         3.182m0-4.991v4.99" />
            </svg>
            <span class="font-bold text-gray-800 text-sm">Ruota pagine</span>
        </div>

        <span class="text-xs text-gray-500 whitespace-nowrap truncate max-w-xs" x-text="original"></span>
        <span class="text-xs text-gray-400 whitespace-nowrap">
            &bull; <span x-text="totalPages"></span> pagine
        </span>

        <div class="h-6 w-px bg-gray-300 mx-1 ml-auto"></div>

        {{-- Ruota tutto +90° --}}
        <button @click="ruotaTutte(90)"
                :disabled="saving"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium
                       border border-gray-300 bg-white text-gray-600
                       hover:bg-green-50 hover:border-green-400 hover:text-green-700
                       disabled:opacity-40 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181
                         3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181
                         3.182m0-4.991v4.99" />
            </svg>
            Ruota tutto +90°
        </button>

        {{-- Ripristina --}}
        <button @click="ripristina()"
                :disabled="saving || !isModificato"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium
                       border border-gray-300 bg-white text-gray-500
                       hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed
                       transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
            </svg>
            Ripristina
        </button>

        {{-- Salva --}}
        <button @click="eseguiRuota()"
                :disabled="saving"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                       bg-green-600 hover:bg-green-700 text-white
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

        <button @click="nuovoRuota()"
                class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium
                       border border-gray-300 bg-white text-gray-500 hover:bg-gray-50 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nuovo
        </button>
    </div>

    {{-- ── BODY: griglia miniature ──────────────────────────── --}}
    <div class="flex-1 overflow-auto p-6">

        {{-- Caricamento --}}
        <div x-show="loading"
             class="flex flex-col items-center justify-center h-full gap-4 text-gray-400">
            <svg class="w-10 h-10 animate-spin text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993
                         0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0
                         0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            <p class="text-sm">Caricamento pagine…</p>
        </div>

        {{-- Legenda --}}
        <div x-show="!loading"
             class="flex items-center gap-2 mb-5 px-4 py-3 bg-white border border-gray-200
                    rounded-xl shadow-sm max-w-7xl mx-auto text-xs text-gray-500">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0
                         001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            <span>
                Clicca su una miniatura (o sull'icona <span class="inline-flex items-center gap-0.5 font-semibold text-green-700">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181
                                 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181
                                 3.182m0-4.991v4.99" />
                    </svg>
                    nel footer</span>)
                per aggiungere <span class="font-semibold text-gray-700">+90°</span> di rotazione in senso orario.
                Ciclo: <span class="font-mono font-semibold text-gray-700">0° → 90° → 180° → 270° → 0°</span>
            </span>
        </div>

        {{-- Griglia --}}
        <div x-show="!loading"
             class="grid gap-4 max-w-7xl mx-auto"
             style="grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));">

            <template x-for="(page, idx) in pages" :key="page.num">
                <div class="relative bg-white rounded-xl shadow-sm border-2 transition-all duration-150 overflow-hidden cursor-pointer
                            select-none group"
                     :class="page.rotation !== 0
                         ? 'border-green-400 shadow-green-100'
                         : 'border-gray-200 hover:border-green-300'"
                     @click="ruotaPagina(idx)">

                    {{-- Area miniatura con rotazione CSS --}}
                    <div class="bg-gray-100 flex items-center justify-center overflow-hidden"
                         style="min-height: 190px;">
                        <div :style="'transform: rotate(' + page.rotation + 'deg); transition: transform 0.25s ease;'">
                            <canvas :id="'canvas-' + page.num"
                                    class="block max-w-full max-h-full object-contain"></canvas>
                        </div>
                        {{-- Spinner --}}
                        <div :id="'spinner-' + page.num"
                             class="absolute inset-0 flex items-center justify-center bg-gray-100">
                            <svg class="w-5 h-5 animate-spin text-green-300" fill="none" viewBox="0 0 24 24"
                                 stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993
                                         0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0
                                         0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-3 py-2 flex items-center justify-between
                                bg-gray-100 border-t border-gray-200">
                        <span class="text-xs text-gray-500 font-semibold" x-text="'Pag. ' + page.num"></span>

                        <div class="flex items-center gap-1.5">
                            {{-- Badge rotazione --}}
                            <span x-show="page.rotation !== 0"
                                  class="text-xs font-bold px-1.5 py-0.5 rounded-md
                                         bg-green-500 text-white leading-none"
                                  x-text="'+' + page.rotation + '°'"></span>
                            <span x-show="page.rotation === 0"
                                  class="text-xs text-gray-400 leading-none">0°</span>

                            {{-- Icona ruota +90° --}}
                            <button @click.stop="ruotaPagina(idx)"
                                    title="+90°"
                                    class="p-1.5 rounded-lg bg-gray-200 text-gray-600
                                           hover:bg-green-500 hover:text-white transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181
                                             3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181
                                             3.182m0-4.991v4.99" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Hint hover --}}
                    <div class="absolute inset-0 flex items-center justify-center
                                bg-green-600 bg-opacity-0 group-hover:bg-opacity-10
                                transition-all duration-150 pointer-events-none">
                        <svg class="w-8 h-8 text-green-600 opacity-0 group-hover:opacity-60 transition-opacity"
                             fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993
                                     0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0
                                     0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </div>
                </div>
            </template>
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
    window._ruotaEditor = {
        file:       '{{ $file }}',
        totalPages: {{ $pages }},
        original:   @json($original),
        routes: {
            applica:  '{{ route('gespidieffe.ruota.applica') }}',
            download: '{{ url('gespidieffe/ruota/download') }}',
            elimina:  '{{ route('gespidieffe.ruota.elimina', ['file' => $file]) }}',
            pdf:      '{{ route('gespidieffe.ruota.pdf', ['file' => $file]) }}',
        },
        csrf: '{{ csrf_token() }}',
    };
</script>

<script>
window._ruotaPdfDoc = null;

function ruotaEditor() {
    return {
        file:       window._ruotaEditor.file,
        totalPages: window._ruotaEditor.totalPages,
        original:   window._ruotaEditor.original,

        // Array: [{num: N, rotation: 0|90|180|270}]
        pages:   [],
        loading: true,
        saving:  false,
        toast:   { show: false, msg: '', type: 'success' },

        get isModificato() {
            return this.pages.some(p => p.rotation !== 0);
        },

        // ── Init ─────────────────────────────────────────────────
        async init() {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

            try {
                window._ruotaPdfDoc = await pdfjsLib.getDocument(
                    window._ruotaEditor.routes.pdf
                ).promise;
            } catch (e) {
                console.error('PDF.js: impossibile caricare il documento', e);
            }

            const arr = [];
            for (let n = 1; n <= this.totalPages; n++) {
                arr.push({ num: n, rotation: 0 });
            }
            this.pages   = arr;
            this.loading = false;

            await this.$nextTick();
            this.renderAllThumbs();
        },

        // ── Rendering ────────────────────────────────────────────
        async renderAllThumbs() {
            if (!window._ruotaPdfDoc) return;
            for (const page of this.pages) {
                await this.renderThumb(page.num);
            }
        },

        async renderThumb(pageNum) {
            if (!window._ruotaPdfDoc) return;
            const canvas  = document.getElementById('canvas-' + pageNum);
            const spinner = document.getElementById('spinner-' + pageNum);
            if (!canvas) return;
            try {
                const pdfPage  = await window._ruotaPdfDoc.getPage(pageNum);
                const viewport = pdfPage.getViewport({ scale: 0.5 });
                canvas.width   = viewport.width;
                canvas.height  = viewport.height;
                await pdfPage.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
                pdfPage.cleanup();
                if (spinner) spinner.style.display = 'none';
            } catch (e) {
                if (spinner) spinner.innerHTML = '<span class="text-xs text-gray-400">errore</span>';
            }
        },

        // ── Ruota singola pagina (+90° per click) ─────────────────
        ruotaPagina(idx) {
            this.pages[idx].rotation = (this.pages[idx].rotation + 90) % 360;
        },

        // ── Ruota tutte le pagine ─────────────────────────────────
        ruotaTutte(deg) {
            this.pages.forEach(p => {
                p.rotation = (p.rotation + deg) % 360;
            });
        },

        // ── Ripristina rotazioni a 0 ──────────────────────────────
        ripristina() {
            this.pages.forEach(p => { p.rotation = 0; });
        },

        // ── Esegui e scarica ─────────────────────────────────────
        async eseguiRuota() {
            if (this.saving) return;
            this.saving = true;
            try {
                const rotazioni = this.pages
                    .filter(p => p.rotation !== 0)
                    .map(p => ({ page: p.num, rotation: p.rotation }));

                const resp = await fetch(window._ruotaEditor.routes.applica, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window._ruotaEditor.csrf,
                    },
                    body: JSON.stringify({
                        file:      this.file,
                        rotazioni: JSON.stringify(rotazioni),
                    }),
                });

                if (!resp.ok) {
                    const err = await resp.json().catch(() => ({}));
                    throw new Error(err.message || 'Errore durante l\'elaborazione');
                }

                const data = await resp.json();

                const a    = document.createElement('a');
                a.download = 'ruotato.pdf';
                a.href     = window._ruotaEditor.routes.download + '/' + data.download_token;
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
            const url  = window._ruotaEditor.routes.elimina;
            const blob = new Blob(
                [JSON.stringify({ _method: 'DELETE', _token: window._ruotaEditor.csrf })],
                { type: 'application/json' }
            );
            navigator.sendBeacon(url, blob);
        },

        nuovoRuota() {
            this.eliminaFile();
            window.location.href = '{{ route('gespidieffe.ruota') }}';
        },
    };
}

window.addEventListener('beforeunload', () => {
    const s = window._ruotaEditor;
    if (!s) return;
    const blob = new Blob(
        [JSON.stringify({ _method: 'DELETE', _token: s.csrf })],
        { type: 'application/json' }
    );
    navigator.sendBeacon(s.routes.elimina, blob);
});
</script>

</x-gespidieffe::layouts.app>
