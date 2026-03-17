<x-gespidieffe::layouts.app breadcrumb="Organizza pagine">

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
@endpush

{{-- =========================================================
     GESPIDIEFFE – Organizza pagine  |  Step 2: Editor

     Layout:
     ┌──────────────────────────────────────────────────────────┐
     │  TOOLBAR (titolo + salva + nuovo)                         │
     ├──────────────────────────────────────────────────────────┤
     │  GRIGLIA MINIATURE (drag&drop) – a piena larghezza        │
     │  Ogni card: miniatura PDF.js + badge numero + azioni      │
     └──────────────────────────────────────────────────────────┘
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
        <span class="text-xs text-gray-400 whitespace-nowrap">
            &bull; <span x-text="pages.length"></span> pagine
            <template x-if="pages.length !== totalPages">
                <span class="text-yellow-600 font-medium">
                    (originale: <span x-text="totalPages"></span>)
                </span>
            </template>
        </span>

        <div class="h-6 w-px bg-gray-300 mx-1 ml-auto"></div>

        {{-- Pulsante annulla modifiche --}}
        <button @click="ripristina()"
                :disabled="saving || !isModificato"
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

        {{-- Pulsante salva --}}
        <button @click="eseguiOrganizza()"
                :disabled="saving || pages.length === 0"
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

    {{-- ── BODY: griglia miniature ──────────────────────────── --}}
    <div class="flex-1 overflow-auto p-6">

        {{-- Placeholder caricamento --}}
        <div x-show="loading"
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
        <div x-show="!loading && pages.length === 0"
             class="flex flex-col items-center justify-center h-full gap-3 text-gray-400">
            <svg class="w-16 h-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5
                         7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12
                         M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125
                         1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            <p class="text-sm font-medium text-gray-400">Tutte le pagine sono state eliminate.</p>
            <button @click="ripristina()"
                    class="text-sm text-yellow-600 underline hover:text-yellow-700">
                Ripristina l'originale
            </button>
        </div>

        {{-- Griglia drag&drop --}}
        <div x-show="!loading && pages.length > 0"
             id="gridContainer"
             class="grid gap-4 max-w-7xl mx-auto"
             style="grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));">

            <template x-for="(page, idx) in pages" :key="page.uid">
                <div class="relative bg-white rounded-xl shadow-sm border border-gray-200
                            hover:shadow-md hover:border-yellow-300 transition-all duration-150
                            cursor-grab active:cursor-grabbing group select-none"
                     :data-uid="page.uid">

                    {{-- Miniatura --}}
                    <div class="relative bg-gray-100 rounded-t-xl overflow-hidden flex items-center justify-center"
                         style="min-height: 190px;">
                        <canvas :id="'canvas-' + page.uid"
                                class="max-w-full max-h-full block object-contain"></canvas>
                        <div :id="'spinner-' + page.uid"
                             class="absolute inset-0 flex items-center justify-center bg-gray-100">
                            <svg class="w-5 h-5 animate-spin text-yellow-300" fill="none" viewBox="0 0 24 24"
                                 stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993
                                         0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0
                                         0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                        </div>
                    </div>

                    {{-- Badge numero pagina originale --}}
                    <div class="absolute top-2 left-2
                                bg-black bg-opacity-50 text-white text-xs font-bold
                                px-1.5 py-0.5 rounded-md leading-none">
                        <span x-text="page.originalPage"></span>
                    </div>

                    {{-- Badge duplicata --}}
                    <div x-show="page.isDuplicate"
                         class="absolute top-2 right-2
                                bg-yellow-500 text-white text-xs font-bold
                                px-1.5 py-0.5 rounded-md leading-none">
                        copia
                    </div>

                    {{-- Footer card: numero posizione + azioni --}}
                    <div class="px-3 py-2 flex items-center justify-between
                                bg-gray-100 border-t border-gray-200 rounded-b-xl">
                        <span class="text-xs text-gray-500 font-semibold" x-text="idx + 1 + ' / ' + pages.length"></span>

                        <div class="flex items-center gap-1">
                            {{-- Duplica --}}
                            <button @click.stop="duplicaPagina(idx)"
                                    title="Duplica pagina"
                                    class="p-1.5 rounded-lg bg-gray-200 text-gray-600
                                           hover:bg-yellow-500 hover:text-white transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125
                                             1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75
                                             a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504
                                             1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0
                                             00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5
                                             4.625v3.375M12 18.75h.008v.008H12v-.008z" />
                                </svg>
                            </button>
                            {{-- Elimina --}}
                            <button @click.stop="chiediElimina(idx)"
                                    title="Elimina pagina"
                                    class="p-1.5 rounded-lg bg-gray-200 text-gray-600
                                           hover:bg-red-500 hover:text-white transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107
                                             1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244
                                             2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456
                                             0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114
                                             1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18
                                             -.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037
                                             -2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0
                                             00-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
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

{{-- ── Script Alpine component ────────────────────────────── --}}
<script>
// pdfDoc salvato FUORI dal Proxy di Alpine
window._organizzaPdfDoc = null;

// Contatore uid univoci per le card (necessario per duplicati)
window._organizzaUidCounter = 0;

function organizzaEditor() {
    return {
        file:       window._organizzaEditor.file,
        totalPages: window._organizzaEditor.totalPages,
        original:   window._organizzaEditor.original,

        // Array di pagine nell'ordine corrente
        // Ogni elemento: { uid: string, originalPage: number, isDuplicate: bool }
        pages: [],

        // Stato originale (per ripristino)
        originalPages: [],

        loading: true,
        saving:  false,

        confirmDialog: { show: false, msg: '', idx: null },
        toast: { show: false, msg: '', type: 'success' },

        // Sortable.js instance
        _sortable: null,

        get isModificato() {
            if (this.pages.length !== this.originalPages.length) return true;
            return this.pages.some((p, i) => p.originalPage !== this.originalPages[i].originalPage);
        },

        // ── Init ─────────────────────────────────────────────────
        async init() {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

            // Carica PDF.js PRIMA di popolare pages, così quando x-for inserisce i canvas
            // il documento è già pronto e non ci sono doppie chiamate a render
            try {
                window._organizzaPdfDoc = await pdfjsLib.getDocument(
                    window._organizzaEditor.routes.pdf
                ).promise;
            } catch (e) {
                console.error('PDF.js: impossibile caricare il documento', e);
            }

            // Costruisce l'array delle pagine: lo facciamo DOPO aver caricato PDF.js,
            // così il x-for parte una sola volta con i canvas già pronti
            const pagesArr = [];
            for (let n = 1; n <= this.totalPages; n++) {
                const uid = 'p' + (++window._organizzaUidCounter);
                pagesArr.push({ uid, originalPage: n, isDuplicate: false });
            }
            this.originalPages = pagesArr.map(p => ({ ...p }));
            this.pages         = pagesArr;   // un solo assign → un solo ciclo x-for

            this.loading = false;

            // Aspetta che Alpine inserisca i canvas nel DOM
            await this.$nextTick();
            this.renderAllThumbs();
            this.initSortable();
        },

        // ── Rendering miniature ──────────────────────────────────
        async renderAllThumbs() {
            if (!window._organizzaPdfDoc) return;
            for (const page of this.pages) {
                await this.renderThumb(page);
            }
        },

        async renderThumb(page) {
            if (!window._organizzaPdfDoc) return;
            const canvas  = document.getElementById('canvas-' + page.uid);
            const spinner = document.getElementById('spinner-' + page.uid);
            if (!canvas) return;
            try {
                const pdfPage  = await window._organizzaPdfDoc.getPage(page.originalPage);
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

        // ── Sortable.js ──────────────────────────────────────────
        initSortable() {
            const grid = document.getElementById('gridContainer');
            if (!grid || typeof Sortable === 'undefined') return;

            this._sortable = Sortable.create(grid, {
                animation:     150,
                ghostClass:    'opacity-40',
                chosenClass:   'ring-2 ring-yellow-400',
                dragClass:     'shadow-xl',
                onEnd: (evt) => {
                    // Sincronizza array Alpine con l'ordine DOM aggiornato da Sortable
                    const uids = Array.from(grid.querySelectorAll('[data-uid]'))
                                      .map(el => el.dataset.uid);
                    const map  = Object.fromEntries(this.pages.map(p => [p.uid, p]));
                    this.pages = uids.map(uid => map[uid]).filter(Boolean);
                },
            });
        },

        // ── Duplica pagina ───────────────────────────────────────
        async duplicaPagina(idx) {
            const orig = this.pages[idx];
            const uid  = 'p' + (++window._organizzaUidCounter);
            const copy = { uid, originalPage: orig.originalPage, isDuplicate: true };

            this.pages.splice(idx + 1, 0, copy);

            // Dopo render DOM, disegna la miniatura e riinizializza Sortable
            await this.$nextTick();
            await this.renderThumb(copy);
            this.reinitSortable();
        },

        // ── Chiedi conferma eliminazione ─────────────────────────
        chiediElimina(idx) {
            const page = this.pages[idx];
            this.confirmDialog = {
                show: true,
                msg:  `Sei sicuro di voler eliminare la pagina ${page.originalPage} (posizione ${idx + 1})?`,
                idx,
            };
        },

        // ── Conferma ed esegui eliminazione ─────────────────────
        async confermaElimina() {
            const idx = this.confirmDialog.idx;
            if (idx !== null && idx >= 0 && idx < this.pages.length) {
                this.pages.splice(idx, 1);
            }
            this.confirmDialog = { show: false, msg: '', idx: null };
            await this.$nextTick();
            this.reinitSortable();
        },

        // ── Ripristina ordine originale ──────────────────────────
        async ripristina() {
            this.pages = this.originalPages.map(p => ({ ...p }));
            await this.$nextTick();
            this.renderAllThumbs();
            this.reinitSortable();
        },

        // ── Reinizializza Sortable dopo modifiche al DOM ─────────
        reinitSortable() {
            if (this._sortable) {
                this._sortable.destroy();
                this._sortable = null;
            }
            this.initSortable();
        },

        // ── Esegui organizzazione e scarica ─────────────────────
        async eseguiOrganizza() {
            if (this.saving || this.pages.length === 0) return;
            this.saving = true;
            try {
                const ordine = this.pages.map(p => p.originalPage).join(',');

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
