<x-gespidieffe::layouts.app breadcrumb="Organizza pagine">

{{-- =========================================================
     GESPIDIEFFE – Organizza pagine  |  Step 1: Upload
     ========================================================= --}}

<div class="flex flex-col w-full min-h-screen bg-gray-50">

    {{-- ── Titolo pagina ───────────────────────────────────────── --}}
    <div class="px-8 pt-8 pb-4 text-center">
        <div class="flex items-center justify-center gap-3 mb-1">
            <svg class="w-7 h-7 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0
                         01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016
                         13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25
                         2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0
                         0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6z
                         M13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25
                         2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Organizza pagine</h1>
        </div>
        <p class="text-sm text-gray-500 mt-1">Riordina, elimina o duplica le pagine di un PDF</p>
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
                      action="{{ route('gespidieffe.organizza.upload') }}"
                      enctype="multipart/form-data"
                      id="uploadForm">
                    @csrf

                    {{-- Drop zone --}}
                    <div id="dropZone"
                         class="relative flex flex-col items-center justify-center
                                border-2 border-dashed border-gray-300 rounded-xl
                                bg-gray-50 hover:bg-yellow-50 hover:border-yellow-400
                                transition-colors duration-200
                                p-10 text-center">

                        <svg class="w-14 h-14 text-yellow-400 mb-4" fill="none" viewBox="0 0 24 24"
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

                        <input type="file"
                               id="pdfInput"
                               name="pdf"
                               accept="application/pdf"
                               class="hidden"
                               required>

                        <button type="button"
                                id="browseBtn"
                                class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold
                                       bg-yellow-500 hover:bg-yellow-600 text-white transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                            </svg>
                            Sfoglia file…
                        </button>
                    </div>

                    {{-- File selezionato --}}
                    <div id="fileInfo" class="hidden mt-4 flex items-center gap-3 bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3">
                        <svg class="w-5 h-5 text-yellow-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5
                                     7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504
                                     -1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504
                                     1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                        <span id="fileName" class="truncate text-sm text-gray-700 flex-1"></span>
                        <span id="fileSize" class="text-xs text-gray-400 flex-shrink-0"></span>
                    </div>

                    {{-- Dimensione massima --}}
                    <p class="text-xs text-gray-400 text-center mt-3">Max 100 MB</p>

                    {{-- Progress bar --}}
                    <div id="progressWrap" class="hidden mt-5">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progressBar"
                                 class="bg-yellow-500 h-2 rounded-full transition-all duration-300"
                                 style="width: 0%"></div>
                        </div>
                        <p id="progressText" class="text-xs text-gray-500 text-center mt-1">Caricamento…</p>
                    </div>

                    {{-- Bottone --}}
                    <button type="submit"
                            id="submitBtn"
                            disabled
                            class="mt-6 w-full flex items-center justify-center gap-2
                                   bg-yellow-500 hover:bg-yellow-600 disabled:bg-gray-300
                                   text-white font-semibold py-3 rounded-xl
                                   transition-colors duration-200 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021
                                     18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                        Carica e organizza le pagine
                    </button>

                </form>
            </div>

            {{-- Info box --}}
            <div class="mt-8 grid grid-cols-2 gap-4 text-sm text-gray-600">
                <div class="flex gap-3 items-start bg-white rounded-xl p-4 shadow-sm">
                    <span class="text-2xl">↕️</span>
                    <div>
                        <p class="font-semibold text-gray-800">Riordina</p>
                        <p class="text-xs text-gray-500 mt-0.5">Trascina le miniature per cambiare l'ordine delle pagine</p>
                    </div>
                </div>
                <div class="flex gap-3 items-start bg-white rounded-xl p-4 shadow-sm">
                    <span class="text-2xl">🗑️</span>
                    <div>
                        <p class="font-semibold text-gray-800">Elimina</p>
                        <p class="text-xs text-gray-500 mt-0.5">Rimuovi le pagine che non ti servono</p>
                    </div>
                </div>
                <div class="flex gap-3 items-start bg-white rounded-xl p-4 shadow-sm">
                    <span class="text-2xl">📋</span>
                    <div>
                        <p class="font-semibold text-gray-800">Duplica</p>
                        <p class="text-xs text-gray-500 mt-0.5">Aggiungi copie di una pagina dove vuoi</p>
                    </div>
                </div>
                <div class="flex gap-3 items-start bg-white rounded-xl p-4 shadow-sm">
                    <span class="text-2xl">⚡</span>
                    <div>
                        <p class="font-semibold text-gray-800">Vettoriale</p>
                        <p class="text-xs text-gray-500 mt-0.5">Qualità e peso originali preservati, nessuna rasterizzazione</p>
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
    const fileInfo  = document.getElementById('fileInfo');
    const fileName  = document.getElementById('fileName');
    const fileSize  = document.getElementById('fileSize');
    const submitBtn = document.getElementById('submitBtn');
    const form      = document.getElementById('uploadForm');
    const progWrap  = document.getElementById('progressWrap');
    const progBar   = document.getElementById('progressBar');
    const progText  = document.getElementById('progressText');

    browseBtn.addEventListener('click', e => {
        e.stopPropagation();
        input.click();
    });

    function showFile(file) {
        if (!file || file.type !== 'application/pdf') {
            fileInfo.classList.add('hidden');
            submitBtn.disabled = true;
            return;
        }
        fileName.textContent = file.name;
        fileSize.textContent = (file.size / 1024 / 1024).toFixed(1) + ' MB';
        fileInfo.classList.remove('hidden');
        submitBtn.disabled = false;
    }

    input.addEventListener('change', () => showFile(input.files[0]));

    // Drag & drop
    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.classList.add('bg-yellow-50', 'border-yellow-400');
    });
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('bg-yellow-50', 'border-yellow-400');
    });
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('bg-yellow-50', 'border-yellow-400');
        const file = Array.from(e.dataTransfer.files).find(f => f.type === 'application/pdf');
        if (!file) return;
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        showFile(file);
    });

    form.addEventListener('submit', e => {
        if (!input.files[0]) { e.preventDefault(); return; }
        submitBtn.disabled = true;
        progWrap.classList.remove('hidden');
        let pct = 0;
        const iv = setInterval(() => {
            pct = Math.min(pct + Math.random() * 12, 90);
            progBar.style.width = pct + '%';
            progText.textContent = 'Caricamento… ' + Math.round(pct) + '%';
        }, 300);
        window.addEventListener('unload', () => clearInterval(iv));
    });
})();
</script>

</x-gespidieffe::layouts.app>