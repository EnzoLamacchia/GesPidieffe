<x-gespidieffe::layouts.app breadcrumb="Merge PDF">

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
@endpush

{{-- =========================================================
     GESPIDIEFFE – Merge PDF  |  Step 2: Editor (riordinamento)
     =========================================================

     Layout:
     ┌─────────────────────────────────────────────────────┐
     │  TOOLBAR (titolo + unisci + nuovo)                   │
     ├─────────────────────────────────────────────────────┤
     │                                                     │
     │  LISTA FILE (card trascinabili con miniatura p.1)   │
     │                                                     │
     └─────────────────────────────────────────────────────┘
     ========================================================= --}}

<div class="flex flex-col w-full h-screen overflow-hidden bg-gray-100"
     x-data="mergeEditor()"
     x-init="init()">

    {{-- ── TOOLBAR ──────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 px-5 py-3 bg-white border-b border-gray-200 shadow-sm flex-shrink-0 flex-wrap">

        {{-- Titolo --}}
        <div class="flex items-center gap-2 mr-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
            </svg>
            <span class="font-bold text-gray-800 text-sm">Merge PDF</span>
        </div>

        {{-- Info file --}}
        <span class="text-xs text-gray-500 whitespace-nowrap">
            <span x-text="files.length"></span> file &bull;
            <span x-text="totalPages"></span> pagine totali
        </span>

        {{-- Separatore --}}
        <div class="h-6 w-px bg-gray-300 mx-1 ml-auto"></div>

        {{-- Unisci e scarica --}}
        <button @@click="unisciEScarica()"
                :disabled="saving"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                       bg-blue-600 hover:bg-blue-700 text-white
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
            <span x-text="saving ? 'Elaborazione…' : 'Unisci e scarica PDF'"></span>
        </button>

        {{-- Aggiungi file --}}
        <button @@click="aggiungiFili()"
                class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium
                       border border-blue-300 bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Aggiungi file…
        </button>

        {{-- Nuovo merge --}}
        <button @@click="nuovoMerge()"
                class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium
                       border border-gray-300 bg-white text-gray-500 hover:bg-gray-50 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993
                         0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0
                         0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Nuovo merge
        </button>
    </div>

    {{-- ── BODY ─────────────────────────────────────────────── --}}
    <div class="flex-1 overflow-auto p-6">

        {{-- Istruzione --}}
        <p class="text-sm text-gray-500 text-center mb-5">
            Trascina le card per cambiare l'ordine dei file nel PDF finale.
        </p>

        {{-- Lista card trascinabili — popolata via JS, non da Alpine x-for --}}
        <div id="sortableList"
             class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 max-w-6xl mx-auto">
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
    window._mergeEditor = {
        session: '{{ $session }}',
        files:   {!! json_encode($manifest['files']) !!},
        routes: {
            applica:  '{{ route('gespidieffe.merge.applica') }}',
            download: '{{ url('gespidieffe/merge/download') }}',
            elimina:  '{{ route('gespidieffe.merge.elimina', ['session' => $session]) }}',
            pdfBase:  '{{ url('gespidieffe/merge/pdf/' . $session) }}',
            aggiungi: '{{ route('gespidieffe.merge.aggiungi', ['session' => $session]) }}',
        },
        csrf: '{{ csrf_token() }}',
    };
    window._mergeSkipCleanup = false;
</script>

{{-- ── Script principale (Alpine component) ─────────────── --}}
<script>
function mergeEditor() {
    return {
        session:    window._mergeEditor.session,
        // "files" è usato solo per totalPages e per costruire l'ordine al salvataggio.
        // Il DOM delle card è gestito interamente da JS/SortableJS — niente x-for.
        files:      window._mergeEditor.files.map(f => ({ ...f })),
        saving:     false,
        toast:      { show: false, msg: '', type: 'success' },

        get totalPages() {
            return this.files.reduce((s, f) => s + (f.pages || 0), 0);
        },

        // ── Init ────────────────────────────────────────────────
        async init() {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

            // Costruisce le card nel DOM (una volta sola)
            this.buildCards();

            // Carica le miniature in parallelo (aggiorna il DOM direttamente)
            await Promise.all(this.files.map(f => this.loadThumb(f)));

            // Attiva SortableJS
            this.initSortable();
        },

        // ── Costruisce le card nel DOM ───────────────────────────
        // Ogni card ha data-index=N (indice fisso del file).
        buildCards() {
            const list = document.getElementById('sortableList');
            list.innerHTML = '';
            this.files.forEach(file => {
                const card = document.createElement('div');
                card.className = 'bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden ' +
                                 'cursor-grab active:cursor-grabbing hover:shadow-md transition-all duration-150 select-none';
                card.dataset.index = file.index;
                card.innerHTML = `
                    <div class="relative bg-gray-100 flex items-center justify-center" style="height:160px;">
                        <img id="thumb-img-${file.index}"
                             class="max-w-full max-h-full object-contain block hidden" />
                        <div id="thumb-spinner-${file.index}"
                             class="absolute inset-0 flex items-center justify-center bg-gray-100">
                            <svg class="w-6 h-6 animate-spin text-blue-400" fill="none" viewBox="0 0 24 24"
                                 stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992
                                         m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7
                                         M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                        </div>
                        <span class="absolute bottom-1 right-1 bg-black bg-opacity-50 text-white
                                     text-xs rounded px-1.5 py-0.5 font-medium">
                            ${file.pages} pag.
                        </span>
                    </div>
                    <div class="px-3 py-2">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-5 h-5 flex items-center justify-center rounded-full
                                         bg-blue-600 text-white text-xs font-bold flex-shrink-0">
                                ${file.index + 1}
                            </span>
                            <p class="text-xs font-medium text-gray-700 truncate"
                               title="${file.original}">${file.original}</p>
                        </div>
                        <div class="flex items-center justify-center text-gray-300 mt-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 6a2 2 0 110-4 2 2 0 010 4zm0 8a2 2 0 110-4 2 2 0 010
                                         4zm0 8a2 2 0 110-4 2 2 0 010 4zm8-16a2 2 0 110-4 2 2 0 010
                                         4zm0 8a2 2 0 110-4 2 2 0 010 4zm0 8a2 2 0 110-4 2 2 0 010 4z"/>
                            </svg>
                        </div>
                    </div>`;
                list.appendChild(card);
            });
        },

        // ── Carica la miniatura della prima pagina e la mostra nella card ──
        async loadThumb(file) {
            const url = window._mergeEditor.routes.pdfBase + '/' + file.index;
            try {
                const pdfDoc   = await pdfjsLib.getDocument(url).promise;
                const page     = await pdfDoc.getPage(1);
                const viewport = page.getViewport({ scale: 0.4 });
                const canvas   = document.createElement('canvas');
                canvas.width   = viewport.width;
                canvas.height  = viewport.height;
                await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
                page.cleanup();
                pdfDoc.destroy();

                const img = document.getElementById('thumb-img-' + file.index);
                const spinner = document.getElementById('thumb-spinner-' + file.index);
                if (img) { img.src = canvas.toDataURL('image/png'); img.classList.remove('hidden'); }
                if (spinner) spinner.remove();
            } catch (e) {
                const spinner = document.getElementById('thumb-spinner-' + file.index);
                if (spinner) spinner.innerHTML = '<span class="text-xs text-gray-400">N/D</span>';
            }
        },

        // ── SortableJS: aggiorna solo l'array interno, non tocca il DOM ──
        initSortable() {
            const list = document.getElementById('sortableList');
            if (!list) return;
            const self = this;
            Sortable.create(list, {
                animation: 150,
                ghostClass: 'opacity-40',
                onEnd(evt) {
                    // SortableJS ha già spostato il nodo nel DOM.
                    // Sincronizziamo l'array interno con lo stesso splice.
                    const moved = self.files.splice(evt.oldIndex, 1)[0];
                    self.files.splice(evt.newIndex, 0, moved);
                },
            });
        },

        // ── Invia ordine al server e scarica il merged PDF ──────
        async unisciEScarica() {
            if (this.saving) return;
            this.saving = true;
            try {
                // Legge l'ordine corrente direttamente dal DOM (source of truth dopo il drag)
                const list  = document.getElementById('sortableList');
                const order = Array.from(list.querySelectorAll('[data-index]'))
                                   .map(el => parseInt(el.dataset.index));

                const resp = await fetch(window._mergeEditor.routes.applica, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window._mergeEditor.csrf,
                    },
                    body: JSON.stringify({ session: this.session, order }),
                });
                if (!resp.ok) {
                    const err = await resp.json().catch(() => ({}));
                    throw new Error(err.message || 'Errore durante il merge');
                }
                const data = await resp.json();
                const a = document.createElement('a');
                a.href     = window._mergeEditor.routes.download + '/' + data.download_token;
                a.download = 'documento_unito.pdf';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                this.showToast('PDF unito scaricato!', 'success');
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

        eliminaSessione() {
            const url  = window._mergeEditor.routes.elimina;
            const blob = new Blob(
                [JSON.stringify({ _method: 'DELETE', _token: window._mergeEditor.csrf })],
                { type: 'application/json' }
            );
            navigator.sendBeacon(url, blob);
        },

        nuovoMerge() {
            this.eliminaSessione();
            window.location.href = '{{ route('gespidieffe.merge') }}';
        },

        aggiungiFili() {
            // Naviga alla pagina aggiungi senza eliminare la sessione
            window._mergeSkipCleanup = true;
            window.location.href = window._mergeEditor.routes.aggiungi;
        },
    };
}

window.addEventListener('beforeunload', () => {
    if (window._mergeSkipCleanup) return;
    const m = window._mergeEditor;
    if (!m) return;
    const blob = new Blob(
        [JSON.stringify({ _method: 'DELETE', _token: m.csrf })],
        { type: 'application/json' }
    );
    navigator.sendBeacon(m.routes.elimina, blob);
});
</script>

</x-gespidieffe::layouts.app>
