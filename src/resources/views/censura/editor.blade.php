<x-gespidieffe::layouts.app breadcrumb="Censura PDF">

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
@endpush

{{-- =========================================================
     GESPIDIEFFE – Censura PDF  |  Step 2: Editor
     =========================================================

     Layout (ispirato a ilovepdf):
     ┌─────────────────────────────────────────────────────┐
     │  TOOLBAR (comandi + salva/scarica)                   │
     ├──────────┬──────────────────────────────────────────┤
     │          │                                          │
     │ MINIATURE│          CANVAS (pagina corrente)        │
     │ (scroll) │                                          │
     │          │                                          │
     └──────────┴──────────────────────────────────────────┘
     ========================================================= --}}

<div class="flex flex-col w-full h-screen overflow-hidden bg-gray-100"
     x-data="censuraEditor()"
     x-init="init()">

    {{-- ── TOOLBAR ──────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 px-5 py-3 bg-white border-b border-gray-200 shadow-sm flex-shrink-0 flex-wrap">

        {{-- Titolo --}}
        <div class="flex items-center gap-2 mr-4">
            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12
                         19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112
                         4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0
                         01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894
                         7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242
                         4.242L9.88 9.88" />
            </svg>
            <span class="font-bold text-gray-800 text-sm">Censura PDF</span>
        </div>

        {{-- Strumento: rettangolo nero --}}
        <button @click="setTool('black')"
                :class="tool === 'black'
                    ? 'bg-gray-900 text-white ring-2 ring-offset-1 ring-gray-900'
                    : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'"
                class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold transition-all">
            <span class="inline-block w-4 h-4 bg-black rounded-sm border border-gray-400"></span>
            Censura nera
        </button>

        {{-- Strumento: rettangolo bianco --}}
        <button @click="setTool('white')"
                :class="tool === 'white'
                    ? 'bg-white text-gray-900 ring-2 ring-offset-1 ring-gray-400 border border-gray-400'
                    : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'"
                class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold transition-all">
            <span class="inline-block w-4 h-4 bg-white rounded-sm border-2 border-gray-400"></span>
            Censura bianca
        </button>

        {{-- Separatore --}}
        <div class="h-6 w-px bg-gray-300 mx-1"></div>

        {{-- Annulla ultimo rettangolo --}}
        <button @click="undoLast()"
                :disabled="rects.length === 0"
                class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium
                       border border-gray-300 bg-white text-gray-700 hover:bg-gray-50
                       disabled:opacity-40 disabled:cursor-not-allowed transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
            </svg>
            Annulla
        </button>

        {{-- Cancella tutti i rettangoli sulla pagina corrente --}}
        <button @click="clearPage()"
                class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium
                       border border-gray-300 bg-white text-gray-700 hover:bg-red-50 hover:border-red-300
                       transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M6 18L18 6M6 6l12 12" />
            </svg>
            Pulisci pagina
        </button>

        {{-- Separatore --}}
        <div class="h-6 w-px bg-gray-300 mx-1 ml-auto"></div>

        {{-- Indicatore rettangoli totali --}}
        <span class="text-xs text-gray-500 whitespace-nowrap">
            <span x-text="rects.length"></span> censure applicate
        </span>

        {{-- Separatore --}}
        <div class="h-6 w-px bg-gray-300 mx-1"></div>

        {{-- Applica e scarica --}}
        <button @click="applicaEscarica()"
                :disabled="rects.length === 0 || saving"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                       bg-red-600 hover:bg-red-700 text-white
                       disabled:bg-gray-300 disabled:cursor-not-allowed
                       transition-colors shadow-sm">
            <svg x-show="!saving" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                 stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021
                         18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"
                 stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993
                         0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0
                         0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            <span x-text="saving ? 'Elaborazione…' : 'Applica e scarica PDF'"></span>
        </button>

        {{-- Ricomincia (torna all'upload) --}}
        <button @click="nuovoFile()"
                class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium
                       border border-gray-300 bg-white text-gray-500 hover:bg-gray-50 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993
                         0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0
                         0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Nuovo file
        </button>
    </div>

    {{-- ── BODY: miniature + canvas ────────────────────────── --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- Colonna miniature (sx) --}}
        <div class="w-36 flex-shrink-0 overflow-y-auto bg-gray-200 border-r border-gray-300 py-3 px-2 space-y-2">
            <template x-for="p in pageCount" :key="p">
                <div @click="goToPage(p)"
                     :class="currentPage === p
                         ? 'ring-2 ring-red-500 bg-white shadow-md'
                         : 'bg-white hover:shadow cursor-pointer'"
                     class="relative rounded-lg overflow-hidden transition-all">

                    {{-- Canvas miniatura --}}
                    <canvas :id="'thumb-' + p"
                            class="w-full block"
                            style="display:block"></canvas>

                    {{-- Numero pagina --}}
                    <div class="absolute bottom-0 left-0 right-0 text-center text-xs
                                bg-black bg-opacity-40 text-white py-0.5">
                        <span x-text="p"></span>
                    </div>

                    {{-- Badge con numero di censure su questa pagina --}}
                    <template x-if="rectsOnPage(p) > 0">
                        <span class="absolute top-1 right-1 bg-red-600 text-white
                                     text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold">
                            <span x-text="rectsOnPage(p)"></span>
                        </span>
                    </template>
                </div>
            </template>
        </div>

        {{-- Area canvas principale (dx) --}}
        <div class="flex-1 overflow-auto bg-gray-400 flex items-start justify-center p-6">

            {{-- Messaggio caricamento miniature --}}
            <div x-show="thumbsLoading"
                 class="flex flex-col items-center justify-center gap-3 text-white mt-24">
                <svg class="w-10 h-10 animate-spin" fill="none" viewBox="0 0 24 24"
                     stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993
                             0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0
                             0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <p class="text-sm font-medium">Caricamento miniature…</p>
                <p class="text-xs opacity-70">Clicca una miniatura per aprire la pagina</p>
            </div>

            {{-- Placeholder prima che l'utente selezioni una pagina --}}
            <div x-show="!thumbsLoading && currentPage === 0"
                 class="flex flex-col items-center justify-center gap-3 text-white mt-24">
                <svg class="w-12 h-12 opacity-60" fill="none" viewBox="0 0 24 24"
                     stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227
                             7.917-3.286-.672zm-7.518-.267A8.25 8.25 0 1120.25 10.5M8.288
                             14.212A5.25 5.25 0 1117.25 10.5" />
                </svg>
                <p class="text-sm font-medium opacity-80">Seleziona una pagina dalla colonna sinistra</p>
            </div>

            <div class="relative shadow-2xl" id="canvasWrap">
                {{-- Canvas di visualizzazione (pagina PDF renderizzata) --}}
                <canvas id="mainCanvas"
                        class="block"
                        style="cursor: crosshair;"></canvas>

                {{-- Canvas di disegno (overlay trasparente per i rettangoli) --}}
                <canvas id="drawCanvas"
                        class="absolute top-0 left-0"
                        style="cursor: crosshair;"></canvas>
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
    window._gespidieffe = {
        token:     '{{ $token }}',
        pageCount: {{ $pageCount }},
        routes: {
            applica:  '{{ route('gespidieffe.censura.applica') }}',
            download: '{{ url('gespidieffe/censura/download') }}',
            elimina:  '{{ route('gespidieffe.censura.elimina', ['file' => $token]) }}',
        },
        csrf: '{{ csrf_token() }}',
    };
</script>

{{-- ── Script principale (Alpine component) ─────────────── --}}
<script>
function censuraEditor() {
    // _pdfDoc fuori dallo stato Alpine: Alpine fa un Proxy che rompe i private fields di PDF.js
    let _pdfDoc     = null;
    // Dimensioni PDF originali in punti per la pagina corrente (usate per conversione coordinate)
    let _pdfWidth   = 0;
    let _pdfHeight  = 0;

    return {
        token:         window._gespidieffe.token,
        pageCount:     window._gespidieffe.pageCount,
        currentPage:   0,   // 0 = nessuna pagina aperta nel canvas principale
        thumbsLoading: true, // true finché non sono caricate tutte le miniature
        tool:          'black',
        rects:         [],
        saving:        false,
        toast:         { show: false, msg: '', type: 'success' },
        scale:         1.5,
        drawing:       false,
        startX:        0,
        startY:        0,

        // ── Init: carica solo le miniature ─────────────────────
        async init() {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

            const pdfUrl = '{{ route('gespidieffe.censura.pdf', ['file' => $token]) }}';
            _pdfDoc = await pdfjsLib.getDocument(pdfUrl).promise;

            // Nasconde il canvas principale finché l'utente non clicca una miniatura
            document.getElementById('mainCanvas').style.display = 'none';
            document.getElementById('drawCanvas').style.display = 'none';

            this.renderAllThumbs().then(() => { this.thumbsLoading = false; });
            this.bindDrawEvents();
        },

        // ── Rendering miniature (una alla volta, senza conflitti) ──
        async renderAllThumbs() {
            const thumbScale = 0.15;
            for (let p = 1; p <= this.pageCount; p++) {
                // Aspetta che il DOM abbia creato il canvas (Alpine x-for è asincrono)
                let canvas = null;
                for (let t = 0; t < 20; t++) {
                    canvas = document.getElementById('thumb-' + p);
                    if (canvas) break;
                    await new Promise(r => setTimeout(r, 50));
                }
                if (!canvas) continue;
                const page     = await _pdfDoc.getPage(p);
                const viewport = page.getViewport({ scale: thumbScale });
                canvas.width  = viewport.width;
                canvas.height = viewport.height;
                await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
                page.cleanup();
            }
        },

        // ── Click miniatura → apre pagina nel canvas principale ──
        async goToPage(num) {
            this.currentPage = num;

            const mainCanvas = document.getElementById('mainCanvas');
            const drawCanvas = document.getElementById('drawCanvas');
            mainCanvas.style.display = 'block';
            drawCanvas.style.display = 'block';

            const page     = await _pdfDoc.getPage(num);
            const viewport = page.getViewport({ scale: this.scale });

            // Salva dimensioni PDF in punti (per conversione coordinate → FPDI)
            _pdfWidth  = page.view[2];  // width in punti PDF
            _pdfHeight = page.view[3];  // height in punti PDF

            mainCanvas.width  = viewport.width;
            mainCanvas.height = viewport.height;
            drawCanvas.width  = viewport.width;
            drawCanvas.height = viewport.height;

            await page.render({ canvasContext: mainCanvas.getContext('2d'), viewport }).promise;
            page.cleanup();

            this.redrawRects();
        },

        setTool(t) { this.tool = t; },

        rectsOnPage(p) {
            return this.rects.filter(r => r.page === p).length;
        },

        // ── Ridisegna rettangoli salvati sul drawCanvas ────────
        redrawRects() {
            const drawCanvas = document.getElementById('drawCanvas');
            if (!drawCanvas || !drawCanvas.width) return;
            const ctx = drawCanvas.getContext('2d');
            ctx.clearRect(0, 0, drawCanvas.width, drawCanvas.height);
            this.rects
                .filter(r => r.page === this.currentPage)
                .forEach(r => {
                    ctx.fillStyle = r.color === 'black' ? '#000' : '#fff';
                    // Le coordinate sono salvate in pixel canvas → disegna direttamente
                    ctx.fillRect(r.cx, r.cy, r.cw, r.ch);
                });
        },

        undoLast() {
            for (let i = this.rects.length - 1; i >= 0; i--) {
                if (this.rects[i].page === this.currentPage) {
                    this.rects.splice(i, 1);
                    break;
                }
            }
            this.redrawRects();
        },

        clearPage() {
            this.rects = this.rects.filter(r => r.page !== this.currentPage);
            this.redrawRects();
        },

        // ── Binding eventi mouse ───────────────────────────────
        bindDrawEvents() {
            const drawCanvas = document.getElementById('drawCanvas');
            const self = this;

            drawCanvas.addEventListener('mousedown', e => {
                if (!self.currentPage) return;
                self.drawing = true;
                const r = drawCanvas.getBoundingClientRect();
                self.startX = e.clientX - r.left;
                self.startY = e.clientY - r.top;
            });

            drawCanvas.addEventListener('mousemove', e => {
                if (!self.drawing) return;
                const r   = drawCanvas.getBoundingClientRect();
                const curX = e.clientX - r.left;
                const curY = e.clientY - r.top;
                self.redrawRects();
                const ctx = drawCanvas.getContext('2d');
                ctx.fillStyle   = self.tool === 'black' ? 'rgba(0,0,0,0.6)' : 'rgba(255,255,255,0.8)';
                ctx.strokeStyle = self.tool === 'black' ? '#000' : '#9CA3AF';
                ctx.lineWidth   = 1;
                ctx.fillRect(self.startX, self.startY, curX - self.startX, curY - self.startY);
                ctx.strokeRect(self.startX, self.startY, curX - self.startX, curY - self.startY);
            });

            drawCanvas.addEventListener('mouseup', e => {
                if (!self.drawing) return;
                self.drawing = false;
                const r    = drawCanvas.getBoundingClientRect();
                const endX = e.clientX - r.left;
                const endY = e.clientY - r.top;
                const w = endX - self.startX;
                const h = endY - self.startY;
                if (Math.abs(w) < 5 || Math.abs(h) < 5) { self.redrawRects(); return; }

                // Normalizza
                const cx = w < 0 ? self.startX + w : self.startX;
                const cy = h < 0 ? self.startY + h : self.startY;
                const cw = Math.abs(w);
                const ch = Math.abs(h);

                // Conversione canvas pixel → punti PDF (per FPDI)
                // PDF.js: origine in alto-sinistra
                // FPDI: origine in alto-sinistra, stessa convenzione
                // Fattore: pdfWidth / canvasWidth = 1/scale
                const pdfX = cx / self.scale;
                const pdfY = cy / self.scale;
                const pdfW = cw / self.scale;
                const pdfH = ch / self.scale;

                self.rects.push({
                    page: self.currentPage,
                    // coordinate canvas (per ridisegno)
                    cx, cy, cw, ch,
                    // coordinate PDF in punti (per backend FPDI)
                    x: pdfX, y: pdfY, w: pdfW, h: pdfH,
                    // altezza pagina in punti (per conversione asse Y in FPDI se necessario)
                    pageH: _pdfHeight,
                    color: self.tool,
                });
                self.redrawRects();
            });
        },

        // ── Applica censure e scarica ──────────────────────────
        async applicaEscarica() {
            if (!this.rects.length || this.saving) return;
            this.saving = true;
            try {
                const resp = await fetch(window._gespidieffe.routes.applica, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window._gespidieffe.csrf,
                    },
                    body: JSON.stringify({ token: this.token, rects: this.rects }),
                });
                if (!resp.ok) {
                    const err = await resp.json().catch(() => ({}));
                    throw new Error(err.message || 'Errore elaborazione');
                }
                const data = await resp.json();
                const a = document.createElement('a');
                a.href     = window._gespidieffe.routes.download + '/' + data.download_token;
                a.download = 'documento_censurato.pdf';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                this.showToast('PDF censurato scaricato!', 'success');
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

        // ── Pulizia file temporanei ────────────────────────────
        eliminaFile() {
            const url = '{{ route('gespidieffe.censura.elimina', ['file' => $token]) }}';
            // sendBeacon funziona anche durante beforeunload (non blocca la navigazione)
            const blob = new Blob([JSON.stringify({ _method: 'DELETE', _token: window._gespidieffe.csrf })],
                                  { type: 'application/json' });
            navigator.sendBeacon(url, blob);
        },

        nuovoFile() {
            this.eliminaFile();
            window.location.href = '{{ route('gespidieffe.censura') }}';
        },
    };
}

// Pulizia automatica alla chiusura tab / navigazione via
window.addEventListener('beforeunload', () => {
    const g = window._gespidieffe;
    if (!g) return;
    const url = g.routes.elimina;
    const blob = new Blob([JSON.stringify({ _method: 'DELETE', _token: g.csrf })],
                          { type: 'application/json' });
    navigator.sendBeacon(url, blob);
});
</script>

</x-gespidieffe::layouts.app>
