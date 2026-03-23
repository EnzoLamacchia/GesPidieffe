<x-gespidieffe::layouts.app breadcrumb="Unisci e Organizza — Passo 2: ordina i file">

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
@endpush

{{-- =========================================================
     GESPIDIEFFE – Unisci e Organizza  |  Step 2: ordina i file
     Layout identico all'editor merge, colori teal, bottone
     "Unisci →" porta allo step 3 (editor organizza).
     ========================================================= --}}

<div class="flex flex-col w-full h-screen overflow-hidden bg-gray-100"
     x-data="uoEditorMerge()"
     x-init="init()">

    {{-- ── TOOLBAR ──────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 px-5 py-3 bg-white border-b border-gray-200 shadow-sm flex-shrink-0 flex-wrap">

        {{-- Titolo + step indicator --}}
        <div class="flex items-center gap-2 mr-4">
            <svg class="w-6 h-6" style="color:#f97316" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M6 6.878V6a2.25 2.25 0 012.25-2.25h7.5A2.25 2.25 0 0118 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 004.5 9v.878m13.5-3A2.25 2.25 0 0119.5 9v.878m0 0a2.246 2.246 0 00-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0121 12v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6c0-.98.626-1.813 1.5-2.122" />
            </svg>
            <span class="font-bold text-gray-800 text-sm">Unisci e Organizza</span>
            <span class="text-xs text-gray-400 font-normal ml-1">— Passo 2 di 3: ordina i file</span>
        </div>

        {{-- Info --}}
        <span class="text-xs text-gray-500 whitespace-nowrap">
            <span x-text="files.length"></span> file &bull;
            <span x-text="totalPages"></span> pagine totali
        </span>

        <div class="h-6 w-px bg-gray-300 mx-1 ml-auto"></div>

        {{-- Unisci → step 3 --}}
        <button @@click="unisciEProcedi()"
                :disabled="saving"
                :style="saving ? 'background:#d1d5db;cursor:not-allowed' : 'background:#ea580c'"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                       text-white transition-colors shadow-sm">
            <svg x-show="!saving" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                 stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
            <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"
                 stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993
                         0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0
                         0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            <span x-text="saving ? 'Unione in corso…' : 'Unisci → Organizza pagine'"></span>
        </button>

        {{-- Aggiungi file --}}
        <button @@click="aggiungiFili()"
                style="border:1px solid #fdba74; background:#fff7ed; color:#ea580c"
                onmouseenter="this.style.background='#fed7aa'"
                onmouseleave="this.style.background='#fff7ed'"
                class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Aggiungi file…
        </button>

        {{-- Ricomincia --}}
        <button @@click="ricomincia()"
                class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium
                       border border-gray-300 bg-white text-gray-500 hover:bg-gray-50 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993
                         0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0
                         0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Ricomincia
        </button>
    </div>

    {{-- ── BODY ─────────────────────────────────────────────── --}}
    <div class="flex-1 overflow-auto p-6">
        <p class="text-sm text-gray-500 text-center mb-5">
            Trascina le card per definire l'ordine dei file nel PDF unito. Poi clicca <strong>Unisci → Organizza pagine</strong>.
        </p>

        <div id="sortableList"
             class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 max-w-6xl mx-auto">
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
         :style="toast.type === 'error' ? 'background:#dc2626' : 'background:#ea580c'"
         class="fixed bottom-6 right-6 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium z-50">
        <span x-text="toast.msg"></span>
    </div>
</div>

{{-- ── Dati PHP → JS ─────────────────────────────────────── --}}
<script>
    window._uoEditorMerge = {
        session: '{{ $session }}',
        files:   {!! json_encode($manifest['files']) !!},
        routes: {
            applicaMerge:  '{{ route('gespidieffe.unisciorganizza.applica-merge') }}',
            elimina:       '{{ route('gespidieffe.unisciorganizza.elimina', ['session' => $session]) }}',
            pdfBase:       '{{ url('gespidieffe/unisci-organizza/pdf-merge/' . $session) }}',
            aggiungi:      '{{ route('gespidieffe.unisciorganizza.aggiungi', ['session' => $session]) }}',
            home:          '{{ route('gespidieffe.unisciorganizza') }}',
        },
        csrf: '{{ csrf_token() }}',
    };
    window._uoSkipCleanup = false;
</script>

<script>
function uoEditorMerge() {
    return {
        session:  window._uoEditorMerge.session,
        files:    window._uoEditorMerge.files.map(f => ({ ...f })),
        saving:   false,
        toast:    { show: false, msg: '', type: 'success' },

        get totalPages() {
            return this.files.reduce((s, f) => s + (f.pages || 0), 0);
        },

        async init() {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
            this.buildCards();
            await Promise.all(this.files.map(f => this.loadThumb(f)));
            this.initSortable();
        },

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
                            <svg class="w-6 h-6 animate-spin" style="color:#fb923c" fill="none" viewBox="0 0 24 24"
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
                                         text-white text-xs font-bold flex-shrink-0" style="background:#ea580c">
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

        async loadThumb(file) {
            const url = window._uoEditorMerge.routes.pdfBase + '/' + file.index;
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

                const img     = document.getElementById('thumb-img-' + file.index);
                const spinner = document.getElementById('thumb-spinner-' + file.index);
                if (img)     { img.src = canvas.toDataURL('image/png'); img.classList.remove('hidden'); }
                if (spinner) spinner.remove();
            } catch (e) {
                const spinner = document.getElementById('thumb-spinner-' + file.index);
                if (spinner) spinner.innerHTML = '<span class="text-xs text-gray-400">N/D</span>';
            }
        },

        initSortable() {
            const list = document.getElementById('sortableList');
            if (!list) return;
            const self = this;
            Sortable.create(list, {
                animation: 150,
                ghostClass: 'opacity-40',
                onEnd(evt) {
                    const moved = self.files.splice(evt.oldIndex, 1)[0];
                    self.files.splice(evt.newIndex, 0, moved);
                },
            });
        },

        async unisciEProcedi() {
            if (this.saving) return;
            this.saving = true;
            try {
                const list  = document.getElementById('sortableList');
                const order = Array.from(list.querySelectorAll('[data-index]'))
                                   .map(el => parseInt(el.dataset.index));

                const resp = await fetch(window._uoEditorMerge.routes.applicaMerge, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window._uoEditorMerge.csrf,
                    },
                    body: JSON.stringify({ session: this.session, order }),
                });

                if (!resp.ok) {
                    const err = await resp.json().catch(() => ({}));
                    throw new Error(err.message || 'Errore durante il merge');
                }

                const data = await resp.json();
                // Non eliminare la sessione: stiamo navigando al passo successivo
                window._uoSkipCleanup = true;
                window.location.href = data.redirect;
            } catch (err) {
                this.showToast(err.message, 'error');
                this.saving = false;
            }
        },

        showToast(msg, type = 'success') {
            this.toast = { show: true, msg, type };
            setTimeout(() => { this.toast.show = false; }, 3500);
        },

        eliminaSessione() {
            const url  = window._uoEditorMerge.routes.elimina;
            const blob = new Blob(
                [JSON.stringify({ _method: 'DELETE', _token: window._uoEditorMerge.csrf })],
                { type: 'application/json' }
            );
            navigator.sendBeacon(url, blob);
        },

        ricomincia() {
            this.eliminaSessione();
            window._uoSkipCleanup = true;
            window.location.href = window._uoEditorMerge.routes.home;
        },

        aggiungiFili() {
            window._uoSkipCleanup = true;
            window.location.href = window._uoEditorMerge.routes.aggiungi;
        },
    };
}

window.addEventListener('beforeunload', () => {
    if (window._uoSkipCleanup) return;
    const m = window._uoEditorMerge;
    if (!m) return;
    const blob = new Blob(
        [JSON.stringify({ _method: 'DELETE', _token: m.csrf })],
        { type: 'application/json' }
    );
    navigator.sendBeacon(m.routes.elimina, blob);
});
</script>

</x-gespidieffe::layouts.app>