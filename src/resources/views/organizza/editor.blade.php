<x-gespidieffe::layouts.app breadcrumb="Organizza pagine">

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <style>
        .sortable-ghost   { opacity: 0.4; }
        .sortable-chosen  { outline: 2px solid #eab308; outline-offset: 2px; }
        .page-selected    { outline: 3px solid #3b82f6 !important; outline-offset: 2px; }
    </style>
@endpush

{{-- =========================================================
     GESPIDIEFFE – Organizza pagine  |  Step 2: Editor

     DOM delle card gestito interamente da JS (come merge/editor),
     NON da Alpine x-for — così Sortable e miniature non confliggono.
     ========================================================= --}}

<div class="flex flex-col w-full h-screen overflow-hidden bg-gray-100"
     x-data="organizzaEditor()"
     x-init="init()">

    {{-- ── TOOLBAR ──────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 px-5 py-3 bg-white border-b border-gray-200 shadow-sm flex-shrink-0 flex-wrap">

        <div class="flex items-center gap-2 mr-4">
            <svg class="w-6 h-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0
                         01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016
                         13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25
                         2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0
                         0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6z
                         M13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25
                         2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
            <span class="font-bold text-gray-800 text-sm">Organizza pagine</span>
        </div>

        <span class="text-xs text-gray-500 whitespace-nowrap truncate max-w-xs" x-text="original"></span>
        <span class="text-xs text-gray-400 whitespace-nowrap" id="badge-pagine">
            &bull; <span id="badge-count">0</span> pagine
        </span>

        {{-- Badge selezione multipla (gestito via JS) --}}
        <span id="badge-selezione"
              class="hidden items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-700 text-xs font-semibold">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span id="badge-selezione-count"></span> selezionate
            <button onclick="organizzaDeselezionaTutte()" class="ml-1 hover:text-blue-900 font-bold">&times;</button>
        </span>

        <div class="h-6 w-px bg-gray-300 mx-1 ml-auto"></div>

        {{-- Ripristina --}}
        <button id="btn-ripristina"
                @click="ripristina()"
                disabled
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium
                       border border-gray-300 bg-white text-gray-500
                       hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed
                       transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
            </svg>
            Ripristina
        </button>

        {{-- Salva --}}
        <button @click="eseguiOrganizza()"
                :disabled="saving"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                       bg-yellow-500 hover:bg-yellow-600 text-white
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

        <button @click="nuovoOrganizza()"
                class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium
                       border border-gray-300 bg-white text-gray-500 hover:bg-gray-50 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nuovo
        </button>
    </div>

    {{-- ── Hint selezione multipla ──────────────────────────── --}}
    <div class="px-5 py-1.5 bg-blue-50 border-b border-blue-100 text-xs text-blue-500 flex-shrink-0">
        Trascina le miniature per riordinarle. Tieni premuto
        <kbd class="px-1 py-0.5 bg-blue-100 rounded font-mono">Ctrl</kbd>
        e clicca per selezionarne più di una, poi trascinale insieme.
    </div>

    {{-- ── BODY ─────────────────────────────────────────────── --}}
    <div class="flex-1 overflow-auto p-6">

        {{-- Spinner iniziale --}}
        <div id="loadingPlaceholder"
             class="flex flex-col items-center justify-center h-full gap-4 text-gray-400">
            <svg class="w-10 h-10 animate-spin text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993
                         0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0
                         0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            <p class="text-sm">Caricamento pagine…</p>
        </div>

        {{-- Placeholder nessuna pagina --}}
        <div id="emptyPlaceholder"
             class="hidden flex-col items-center justify-center h-full gap-3 text-gray-400">
            <svg class="w-16 h-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5
                         7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12
                         M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125
                         1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            <p class="text-sm font-medium text-gray-400">Tutte le pagine sono state eliminate.</p>
            <button onclick="document.querySelector('[x-data]').__x.$data.ripristina()"
                    class="text-sm text-yellow-600 underline hover:text-yellow-700">
                Ripristina l'originale
            </button>
        </div>

        {{-- Griglia card — popolata interamente da JS --}}
        <div id="gridContainer"
             class="hidden grid gap-4 max-w-7xl mx-auto"
             style="grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));">
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
                    <p class="font-semibold text-gray-900 text-sm">Elimina pagina</p>
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
                    Elimina
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
    window._organizzaEditor = {
        file:       '{{ $file }}',
        totalPages: {{ $pages }},
        original:   @json($original),
        routes: {
            applica:  '{{ route('gespidieffe.organizza.applica') }}',
            download: '{{ url('gespidieffe/organizza/download') }}',
            elimina:  '{{ route('gespidieffe.organizza.elimina', ['file' => $file]) }}',
            pdf:      '{{ route('gespidieffe.organizza.pdf', ['file' => $file]) }}',
        },
        csrf: '{{ csrf_token() }}',
    };
</script>

<script>
// ── Stato globale (fuori da Alpine per evitare proxy) ────────
window._orgPdfDoc    = null;   // documento PDF.js
window._orgPages     = [];     // [{uid, originalPage, isDuplicate}] — source of truth
window._orgOriginal  = [];     // copia immutabile per ripristino
window._orgSelected  = {};     // uid → true  (selezione multipla)
window._orgUidCount  = 0;

// ── Helpers globali richiamati da onclick inline ─────────────
function organizzaDeselezionaTutte() {
    window._orgSelected = {};
    document.querySelectorAll('#gridContainer > [data-uid]').forEach(el => {
        el.classList.remove('page-selected');
    });
    _organizzaAggiornaBadgeSelezione();
}

function _organizzaAggiornaBadgeSelezione() {
    const n   = Object.keys(window._orgSelected).length;
    const el  = document.getElementById('badge-selezione');
    const cnt = document.getElementById('badge-selezione-count');
    if (n > 0) {
        el.classList.remove('hidden');
        el.classList.add('flex');
        cnt.textContent = n;
    } else {
        el.classList.add('hidden');
        el.classList.remove('flex');
    }
}

function organizzaEditor() {
    return {
        file:       window._organizzaEditor.file,
        totalPages: window._organizzaEditor.totalPages,
        original:   window._organizzaEditor.original,

        saving:        false,
        confirmDialog: { show: false, msg: '', uid: null },
        toast:         { show: false, msg: '', type: 'success' },

        // ── Init ─────────────────────────────────────────────────
        async init() {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

            try {
                window._orgPdfDoc = await pdfjsLib.getDocument(
                    window._organizzaEditor.routes.pdf
                ).promise;
            } catch (e) {
                console.error('PDF.js: impossibile caricare', e);
            }

            // Costruisce l'array di pagine
            const arr = [];
            for (let n = 1; n <= this.totalPages; n++) {
                arr.push({ uid: 'p' + (++window._orgUidCount), originalPage: n, isDuplicate: false });
            }
            window._orgOriginal = arr.map(p => ({ ...p }));
            window._orgPages    = arr;

            // Costruisce il DOM e carica le miniature
            this._buildGrid();
            await this._renderAllThumbs();
            this._initSortable();

            document.getElementById('loadingPlaceholder').classList.add('hidden');
            this._aggiornaBadgeCount();
            this._aggiornaRipristina();
        },

        // ── Costruisce le card nel DOM ───────────────────────────
        _buildGrid() {
            const grid = document.getElementById('gridContainer');
            grid.innerHTML = '';

            window._orgPages.forEach((page, idx) => {
                grid.appendChild(this._creaCard(page, idx));
            });

            this._mostraGriglia();
        },

        // ── Crea una singola card ────────────────────────────────
        _creaCard(page, idx) {
            const div = document.createElement('div');
            div.className = 'relative bg-white rounded-xl shadow-sm border border-gray-200 ' +
                            'hover:shadow-md hover:border-yellow-300 transition-all duration-150 ' +
                            'cursor-grab active:cursor-grabbing select-none';
            div.dataset.uid = page.uid;

            const badgeCopia = page.isDuplicate
                ? `<div class="absolute top-2 right-2 bg-yellow-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-md leading-none">copia</div>`
                : '';

            const posLabel = (idx + 1) + ' / ' + window._orgPages.length;

            div.innerHTML = `
                <div class="relative bg-gray-100 rounded-t-xl overflow-hidden flex items-center justify-center" style="min-height:190px;">
                    <canvas id="canvas-${page.uid}" class="max-w-full max-h-full block object-contain"></canvas>
                    <div id="spinner-${page.uid}" class="absolute inset-0 flex items-center justify-center bg-gray-100">
                        <svg class="w-5 h-5 animate-spin text-yellow-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993
                                     0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0
                                     0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                    </div>
                </div>
                <div class="absolute top-2 left-2 bg-black bg-opacity-50 text-white text-xs font-bold px-1.5 py-0.5 rounded-md leading-none">
                    ${page.originalPage}
                </div>
                ${badgeCopia}
                <div class="px-3 py-2 flex items-center justify-between bg-gray-100 border-t border-gray-200 rounded-b-xl">
                    <span class="pos-label text-xs text-gray-500 font-semibold">${posLabel}</span>
                    <div class="flex items-center gap-1">
                        <button data-action="duplica" data-action-uid="${page.uid}"
                                title="Duplica pagina"
                                class="p-1.5 rounded-lg bg-gray-200 text-gray-600 hover:bg-yellow-500 hover:text-white transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125
                                         1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75
                                         a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504
                                         1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0
                                         00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5
                                         4.625v3.375M12 18.75h.008v.008H12v-.008z"/>
                            </svg>
                        </button>
                        <button data-action="elimina" data-action-uid="${page.uid}"
                                title="Elimina pagina"
                                class="p-1.5 rounded-lg bg-gray-200 text-gray-600 hover:bg-red-500 hover:text-white transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107
                                         1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244
                                         2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456
                                         0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114
                                         1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18
                                         -.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037
                                         -2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                        </button>
                    </div>
                </div>`;

            // Click sulla card: Ctrl+click per selezione multipla
            div.addEventListener('click', (evt) => {
                if (!evt.ctrlKey && !evt.metaKey) return;
                evt.preventDefault();
                const uid = div.dataset.uid;
                if (window._orgSelected[uid]) {
                    delete window._orgSelected[uid];
                    div.classList.remove('page-selected');
                } else {
                    window._orgSelected[uid] = true;
                    div.classList.add('page-selected');
                }
                _organizzaAggiornaBadgeSelezione();
            });

            // Pulsanti azioni
            div.addEventListener('click', (evt) => {
                const btn = evt.target.closest('[data-action]');
                if (!btn) return;
                evt.stopPropagation();
                const uid = btn.dataset.actionUid;
                if (btn.dataset.action === 'duplica') this.duplicaPagina(uid);
                if (btn.dataset.action === 'elimina') this.chiediElimina(uid);
            });

            return div;
        },

        // ── Renderizza tutte le miniature ────────────────────────
        async _renderAllThumbs() {
            if (!window._orgPdfDoc) return;
            for (const page of window._orgPages) {
                await this._renderThumb(page);
            }
        },

        async _renderThumb(page) {
            if (!window._orgPdfDoc) return;
            const canvas  = document.getElementById('canvas-' + page.uid);
            const spinner = document.getElementById('spinner-' + page.uid);
            if (!canvas) return;
            try {
                const pdfPage  = await window._orgPdfDoc.getPage(page.originalPage);
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

        // ── Sortable ─────────────────────────────────────────────
        _initSortable() {
            const grid = document.getElementById('gridContainer');
            if (!grid || typeof Sortable === 'undefined') return;
            const self = this;

            Sortable.create(grid, {
                animation:   150,
                ghostClass:  'sortable-ghost',
                chosenClass: 'sortable-chosen',

                onStart(evt) {
                    const uid = evt.item.dataset.uid;
                    // Se la card trascinata non è nel gruppo selezionato, deseleziona tutto
                    if (!window._orgSelected[uid]) {
                        organizzaDeselezionaTutte();
                    }
                },

                onEnd(evt) {
                    const uid    = evt.item.dataset.uid;
                    const selUids = Object.keys(window._orgSelected);

                    if (selUids.length > 1 && window._orgSelected[uid]) {
                        // ── Blocco: sposta tutte le selezionate ──────────
                        const anchorEl = grid.querySelector(':scope > [data-uid="' + uid + '"]');

                        const blocco = window._orgPages.filter(p => window._orgSelected[p.uid]);
                        const bloccoEls = blocco
                            .map(p => grid.querySelector(':scope > [data-uid="' + p.uid + '"]'))
                            .filter(el => el && el !== anchorEl);

                        let dopo = anchorEl;
                        for (const el of bloccoEls) {
                            if (dopo.nextSibling) {
                                grid.insertBefore(el, dopo.nextSibling);
                            } else {
                                grid.appendChild(el);
                            }
                            dopo = el;
                        }

                        // Sincronizza _orgPages con il nuovo ordine DOM
                        const nuoviUids = Array.from(
                            grid.querySelectorAll(':scope > [data-uid]')
                        ).map(el => el.dataset.uid);
                        const map = Object.fromEntries(window._orgPages.map(p => [p.uid, p]));
                        window._orgPages = nuoviUids.map(u => map[u]).filter(Boolean);

                    } else {
                        // ── Singolo: sincronizza con splice ──────────────
                        const moved = window._orgPages.splice(evt.oldIndex, 1)[0];
                        window._orgPages.splice(evt.newIndex, 0, moved);
                    }

                    self._aggiornaFooterPositions();
                    self._aggiornaBadgeCount();
                    self._aggiornaRipristina();
                },
            });
        },

        // ── Aggiorna i label "pos / tot" nel footer di ogni card ─
        _aggiornaFooterPositions() {
            const cards = Array.from(
                document.querySelectorAll('#gridContainer > [data-uid]')
            );
            const tot = cards.length;
            cards.forEach((card, idx) => {
                const lbl = card.querySelector('.pos-label');
                if (lbl) lbl.textContent = (idx + 1) + ' / ' + tot;
            });
        },

        _aggiornaBadgeCount() {
            const n = window._orgPages.length;
            document.getElementById('badge-count').textContent = n;
            this._mostraGriglia();
        },

        _mostraGriglia() {
            const grid  = document.getElementById('gridContainer');
            const empty = document.getElementById('emptyPlaceholder');
            if (window._orgPages.length === 0) {
                grid.classList.add('hidden');
                empty.classList.remove('hidden');
                empty.classList.add('flex');
            } else {
                grid.classList.remove('hidden');
                empty.classList.add('hidden');
                empty.classList.remove('flex');
            }
        },

        _aggiornaRipristina() {
            const orig = window._orgOriginal;
            const curr = window._orgPages;
            const btn  = document.getElementById('btn-ripristina');
            if (!btn) return;
            const modificato = curr.length !== orig.length ||
                curr.some((p, i) => p.originalPage !== orig[i].originalPage);
            btn.disabled = !modificato;
        },

        // ── Duplica pagina ───────────────────────────────────────
        async duplicaPagina(uid) {
            const idx  = window._orgPages.findIndex(p => p.uid === uid);
            if (idx < 0) return;
            const orig = window._orgPages[idx];
            const copy = { uid: 'p' + (++window._orgUidCount), originalPage: orig.originalPage, isDuplicate: true };
            window._orgPages.splice(idx + 1, 0, copy);

            // Inserisce la nuova card subito dopo quella originale nel DOM
            const grid    = document.getElementById('gridContainer');
            const cards   = Array.from(grid.querySelectorAll(':scope > [data-uid]'));
            const refCard = cards[idx];
            const newCard = this._creaCard(copy, idx + 1);
            if (refCard && refCard.nextSibling) {
                grid.insertBefore(newCard, refCard.nextSibling);
            } else {
                grid.appendChild(newCard);
            }

            this._aggiornaFooterPositions();
            this._aggiornaBadgeCount();
            this._aggiornaRipristina();
            await this._renderThumb(copy);
        },

        // ── Chiedi conferma eliminazione ─────────────────────────
        chiediElimina(uid) {
            const page = window._orgPages.find(p => p.uid === uid);
            if (!page) return;
            const idx = window._orgPages.indexOf(page);
            this.confirmDialog = {
                show: true,
                msg:  `Sei sicuro di voler eliminare la pagina ${page.originalPage} (posizione ${idx + 1})?`,
                uid,
            };
        },

        confermaElimina() {
            const uid = this.confirmDialog.uid;
            const idx = window._orgPages.findIndex(p => p.uid === uid);
            if (idx >= 0) {
                window._orgPages.splice(idx, 1);
                const card = document.querySelector(`#gridContainer [data-uid="${uid}"]`);
                if (card) card.remove();
            }
            this.confirmDialog = { show: false, msg: '', uid: null };
            this._aggiornaFooterPositions();
            this._aggiornaBadgeCount();
            this._aggiornaRipristina();
        },

        // ── Ripristina ordine originale ──────────────────────────
        async ripristina() {
            window._orgPages    = window._orgOriginal.map(p => ({ ...p }));
            window._orgSelected = {};
            _organizzaAggiornaBadgeSelezione();
            this._buildGrid();
            await this._renderAllThumbs();
            this._aggiornaFooterPositions();
            this._aggiornaBadgeCount();
            this._aggiornaRipristina();
            this._initSortable();
        },

        // ── Salva PDF ────────────────────────────────────────────
        async eseguiOrganizza() {
            if (this.saving || window._orgPages.length === 0) return;
            this.saving = true;
            try {
                // Source of truth: legge dal DOM
                const ordine = Array.from(
                    document.querySelectorAll('#gridContainer > [data-uid]')
                ).map(el => {
                    const uid  = el.dataset.uid;
                    const page = window._orgPages.find(p => p.uid === uid);
                    return page ? page.originalPage : null;
                }).filter(Boolean).join(',');

                const resp = await fetch(window._organizzaEditor.routes.applica, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window._organizzaEditor.csrf,
                    },
                    body: JSON.stringify({ file: this.file, ordine }),
                });

                if (!resp.ok) {
                    const err = await resp.json().catch(() => ({}));
                    throw new Error(err.message || 'Errore durante l\'elaborazione');
                }

                const data = await resp.json();
                const a    = document.createElement('a');
                a.download = 'organizzato.pdf';
                a.href     = window._organizzaEditor.routes.download + '/' + data.download_token;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                this.showToast('PDF organizzato scaricato!', 'success');
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
            const url  = window._organizzaEditor.routes.elimina;
            const blob = new Blob(
                [JSON.stringify({ _method: 'DELETE', _token: window._organizzaEditor.csrf })],
                { type: 'application/json' }
            );
            navigator.sendBeacon(url, blob);
        },

        nuovoOrganizza() {
            this.eliminaFile();
            window.location.href = '{{ route('gespidieffe.organizza') }}';
        },
    };
}

window.addEventListener('beforeunload', () => {
    const s = window._organizzaEditor;
    if (!s) return;
    const blob = new Blob(
        [JSON.stringify({ _method: 'DELETE', _token: s.csrf })],
        { type: 'application/json' }
    );
    navigator.sendBeacon(s.routes.elimina, blob);
});
</script>

</x-gespidieffe::layouts.app>
