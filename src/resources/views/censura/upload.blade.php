<x-gespidieffe::layouts.app breadcrumb="Censura PDF">

{{-- =========================================================
     GESPIDIEFFE – Censura PDF  |  Step 1: Upload
     ========================================================= --}}

<div class="flex flex-col w-full min-h-screen bg-gray-50">

    {{-- ── Titolo pagina ───────────────────────────────────────── --}}
    <div class="px-8 pt-8 pb-4 text-center">
        <div class="flex items-center justify-center gap-3 mb-1">
            <svg class="w-7 h-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993
                         0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162
                         10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65
                         3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242
                         4.242L9.88 9.88" />
            </svg>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Censura PDF</h1>
        </div>
        <p class="text-sm text-gray-500 mt-1">Copri le parti sensibili del documento con rettangoli neri o bianchi</p>
    </div>

    {{-- ── Area centrale ─────────────────────────────────────── --}}
    <div class="flex flex-1 items-center justify-center px-4 py-6">
        <div class="w-full max-w-xl">

            {{-- Errori di validazione --}}
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Card upload --}}
            <div class="bg-white rounded-2xl shadow-md p-8">

                <form method="POST"
                      action="{{ route('gespidieffe.censura.upload') }}"
                      enctype="multipart/form-data"
                      id="uploadForm">
                    @csrf

                    {{-- Drop zone --}}
                    <div id="dropZone"
                         class="relative flex flex-col items-center justify-center
                                border-2 border-dashed border-gray-300 rounded-xl
                                bg-gray-50 hover:bg-red-50 hover:border-red-400
                                transition-colors duration-200
                                p-10 text-center">

                        <svg class="w-14 h-14 text-red-400 mb-4" fill="none" viewBox="0 0 24 24"
                             stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125
                                     1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75
                                     12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125
                                     1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504
                                     1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>

                        <p class="text-gray-700 font-semibold text-base">
                            Trascina qui il file PDF
                        </p>
                        <p class="text-gray-400 text-sm mt-1">oppure usa il pulsante qui sotto</p>
                        <p id="fileName" class="mt-3 text-sm font-medium text-red-600 hidden"></p>

                        <input type="file"
                               id="pdfInput"
                               name="pdf"
                               accept="application/pdf"
                               class="hidden"
                               required>

                        {{-- Pulsante sfoglia filesystem --}}
                        <button type="button"
                                id="browseBtn"
                                class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold
                                       bg-red-600 hover:bg-red-700 text-white transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                            </svg>
                            Sfoglia file…
                        </button>
                    </div>

                    {{-- Dimensione massima --}}
                    <p class="text-xs text-gray-400 text-center mt-3">Dimensione massima: 50 MB</p>

                    {{-- Progress bar (nascosta finché non parte l'upload) --}}
                    <div id="progressWrap" class="hidden mt-5">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progressBar"
                                 class="bg-red-500 h-2 rounded-full transition-all duration-300"
                                 style="width: 0%"></div>
                        </div>
                        <p id="progressText" class="text-xs text-gray-500 text-center mt-1">Caricamento…</p>
                    </div>

                    {{-- Bottone --}}
                    <button type="submit"
                            id="submitBtn"
                            class="mt-6 w-full flex items-center justify-center gap-2
                                   bg-red-600 hover:bg-red-700 disabled:bg-gray-300
                                   text-white font-semibold py-3 rounded-xl
                                   transition-colors duration-200 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021
                                     18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                        Carica e apri editor
                    </button>

                </form>
            </div>

            {{-- Descrizione funzionalità --}}
            <div class="mt-8 grid grid-cols-2 gap-4 text-sm text-gray-600">
                <div class="flex gap-3 items-start bg-white rounded-xl p-4 shadow-sm">
                    <span class="text-2xl">⬛</span>
                    <div>
                        <p class="font-semibold text-gray-800">Rettangolo nero</p>
                        <p class="text-xs text-gray-500 mt-0.5">Copre il testo con un blocco nero opaco</p>
                    </div>
                </div>
                <div class="flex gap-3 items-start bg-white rounded-xl p-4 shadow-sm">
                    <span class="text-2xl">⬜</span>
                    <div>
                        <p class="font-semibold text-gray-800">Rettangolo bianco</p>
                        <p class="text-xs text-gray-500 mt-0.5">Copre il testo con un blocco bianco</p>
                    </div>
                </div>
                <div class="flex gap-3 items-start bg-white rounded-xl p-4 shadow-sm col-span-2">
                    <span class="text-2xl">🔒</span>
                    <div>
                        <p class="font-semibold text-gray-800">Censura permanente</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Il testo sottostante viene eliminato definitivamente dal file PDF.
                            Non è possibile copiarlo né recuperarlo.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    const dropZone  = document.getElementById('dropZone');
    const browseBtn = document.getElementById('browseBtn');
    const input     = document.getElementById('pdfInput');
    const fileLabel = document.getElementById('fileName');
    const form      = document.getElementById('uploadForm');
    const submitBtn = document.getElementById('submitBtn');
    const progWrap  = document.getElementById('progressWrap');
    const progBar   = document.getElementById('progressBar');
    const progText  = document.getElementById('progressText');

    browseBtn.addEventListener('click', e => {
        e.stopPropagation();
        input.click();
    });

    function showFile(file) {
        if (!file) return;
        fileLabel.textContent = file.name;
        fileLabel.classList.remove('hidden');
    }

    input.addEventListener('change', () => showFile(input.files[0]));

    // Drag & drop
    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.classList.add('bg-red-50', 'border-red-400');
    });
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('bg-red-50', 'border-red-400');
    });
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('bg-red-50', 'border-red-400');
        const file = e.dataTransfer.files[0];
        if (file && file.type === 'application/pdf') {
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            showFile(file);
        }
    });

    // Simula progressione durante il submit (upload reale via form POST)
    form.addEventListener('submit', e => {
        if (!input.files.length) { e.preventDefault(); return; }
        submitBtn.disabled = true;
        progWrap.classList.remove('hidden');
        let pct = 0;
        const iv = setInterval(() => {
            pct = Math.min(pct + Math.random() * 15, 90);
            progBar.style.width = pct + '%';
            progText.textContent = 'Caricamento… ' + Math.round(pct) + '%';
        }, 300);
        // Pulizia non strettamente necessaria (la pagina cambierà)
        window.addEventListener('unload', () => clearInterval(iv));
    });
})();
</script>

</x-gespidieffe::layouts.app>
