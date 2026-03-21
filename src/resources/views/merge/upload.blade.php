<x-gespidieffe::layouts.app breadcrumb="Merge PDF">

{{-- =========================================================
     GESPIDIEFFE – Merge PDF  |  Step 1: Upload
     ========================================================= --}}

<div class="flex flex-col w-full min-h-screen bg-gray-50">

    {{-- ── Titolo pagina ───────────────────────────────────────── --}}
    <div class="px-8 pt-8 pb-4 text-center">
        <div class="flex items-center justify-center gap-3 mb-1">
            <svg class="w-7 h-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
            </svg>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                @if(isset($existingSession)) Aggiungi file al merge @else Merge PDF @endif
            </h1>
        </div>
        <p class="text-sm text-gray-500 mt-1">
            @if(isset($existingSession))
                Seleziona i nuovi PDF da aggiungere alla sessione in corso
            @else
                Unisci più file PDF in un unico documento
            @endif
        </p>
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
                      action="{{ route('gespidieffe.merge.upload') }}"
                      enctype="multipart/form-data"
                      id="uploadForm">
                    @csrf
                    @if(isset($existingSession))
                        <input type="hidden" name="existing_session" value="{{ $existingSession }}">
                    @endif

                    {{-- File già in sessione (solo in modalità aggiungi) --}}
                    @if(isset($existingFiles))
                        <div class="mb-5">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                                Già nella sessione ({{ count($existingFiles) }} file)
                            </p>
                            <ul class="space-y-1.5">
                                @foreach($existingFiles as $ef)
                                    <li class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm">
                                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5
                                                     7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504
                                                     -1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504
                                                     1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                        </svg>
                                        <span class="truncate text-gray-500 flex-1">{{ $ef['original'] }}</span>
                                        <span class="text-xs text-gray-400 flex-shrink-0">{{ $ef['pages'] }} pag.</span>
                                        <svg class="w-3.5 h-3.5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="text-xs text-blue-600 mt-2">Seleziona qui sotto i file da aggiungere:</p>
                        </div>
                    @endif

                    {{-- Drop zone (click apre il selettore file) --}}
                    <div id="dropZone"
                         class="relative flex flex-col items-center justify-center
                                border-2 border-dashed border-gray-300 rounded-xl
                                bg-gray-50 hover:bg-blue-50 hover:border-blue-400
                                transition-colors duration-200
                                p-10 text-center">

                        <svg class="w-14 h-14 text-blue-400 mb-4" fill="none" viewBox="0 0 24 24"
                             stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125
                                     1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75
                                     12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125
                                     1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504
                                     1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>

                        <p class="text-gray-700 font-semibold text-base">
                            Trascina qui i file PDF
                        </p>
                        <p class="text-gray-400 text-sm mt-1">oppure usa il pulsante qui sotto (min. 2, max. 20)</p>

                        <input type="file"
                               id="pdfsInput"
                               name="pdfs[]"
                               accept="application/pdf"
                               class="hidden"
                               multiple>

                        {{-- Pulsante sfoglia filesystem --}}
                        <button type="button"
                                id="browseBtn"
                                class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold
                                       bg-blue-600 hover:bg-blue-700 text-white transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                            </svg>
                            Sfoglia file…
                        </button>
                    </div>

                    {{-- Lista file selezionati --}}
                    <ul id="fileList" class="mt-4 space-y-2 hidden"></ul>

                    {{-- Pulsante aggiungi altri file (visibile solo dopo la prima selezione) --}}
                    <div id="addMoreWrap" class="hidden mt-3 flex justify-center">
                        <button type="button"
                                id="addMoreBtn"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium
                                       border border-blue-300 text-blue-600 bg-blue-50
                                       hover:bg-blue-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Aggiungi altri file…
                        </button>
                        <input type="file" id="addMoreInput" accept="application/pdf" class="hidden" multiple>
                    </div>

                    {{-- Alert: serve almeno 1 altro file (solo in modalità normale) --}}
                    <div id="alertMinFile"
                         class="hidden mt-3 flex items-start gap-2 px-4 py-3 bg-amber-50
                                border border-amber-300 rounded-lg text-sm text-amber-800">
                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-amber-500" fill="none"
                             viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948
                                     3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949
                                     3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        Per eseguire il merge occorrono almeno 2 file. Aggiungi un secondo PDF.
                    </div>

                    {{-- Dimensione massima --}}
                    <p class="text-xs text-gray-400 text-center mt-3">Max 50 MB per file &bull; Da 2 a 20 file</p>

                    {{-- Progress bar --}}
                    <div id="progressWrap" class="hidden mt-5">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progressBar"
                                 class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                                 style="width: 0%"></div>
                        </div>
                        <p id="progressText" class="text-xs text-gray-500 text-center mt-1">Caricamento…</p>
                    </div>

                    {{-- Bottone --}}
                    <button type="submit"
                            id="submitBtn"
                            disabled
                            class="mt-6 w-full flex items-center justify-center gap-2
                                   bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300
                                   text-white font-semibold py-3 rounded-xl
                                   transition-colors duration-200 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021
                                     18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                        @if(isset($existingSession)) Aggiungi al merge @else Carica e organizza @endif
                    </button>

                </form>
            </div>

            {{-- Info box --}}
            <div class="mt-8 grid grid-cols-2 gap-4 text-sm text-gray-600">
                <div class="flex gap-3 items-start bg-white rounded-xl p-4 shadow-sm">
                    <span class="text-2xl">📄</span>
                    <div>
                        <p class="font-semibold text-gray-800">Più file PDF</p>
                        <p class="text-xs text-gray-500 mt-0.5">Carica da 2 a 20 file PDF da unire</p>
                    </div>
                </div>
                <div class="flex gap-3 items-start bg-white rounded-xl p-4 shadow-sm">
                    <span class="text-2xl">🔀</span>
                    <div>
                        <p class="font-semibold text-gray-800">Riordina</p>
                        <p class="text-xs text-gray-500 mt-0.5">Trascina i file per scegliere l'ordine finale</p>
                    </div>
                </div>
                <div class="flex gap-3 items-start bg-white rounded-xl p-4 shadow-sm col-span-2">
                    <span class="text-2xl">⚡</span>
                    <div>
                        <p class="font-semibold text-gray-800">Merge vettoriale</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            I PDF vengono uniti senza rasterizzazione: qualità e peso originali preservati.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    const isAggiungi  = {{ isset($existingSession) ? 'true' : 'false' }};
    const dropZone    = document.getElementById('dropZone');
    const browseBtn   = document.getElementById('browseBtn');
    const input       = document.getElementById('pdfsInput');
    const addMoreWrap = document.getElementById('addMoreWrap');
    const addMoreBtn  = document.getElementById('addMoreBtn');
    const addMoreInput= document.getElementById('addMoreInput');
    const fileList    = document.getElementById('fileList');
    const alertMin    = document.getElementById('alertMinFile');
    const submitBtn   = document.getElementById('submitBtn');
    const form        = document.getElementById('uploadForm');
    const progWrap    = document.getElementById('progressWrap');
    const progBar     = document.getElementById('progressBar');
    const progText    = document.getElementById('progressText');

    // Stato: array di File accumulati
    let selectedFiles = [];

    // ── Aggiunge file evitando duplicati (nome + dimensione) ────
    function addFiles(newFiles) {
        newFiles.forEach(f => {
            const dup = selectedFiles.some(e => e.name === f.name && e.size === f.size);
            if (!dup && selectedFiles.length < 20) selectedFiles.push(f);
        });
        renderList();
    }

    // ── Rimuove un file per indice ──────────────────────────────
    function removeFile(idx) {
        selectedFiles.splice(idx, 1);
        renderList();
    }

    // ── Ridisegna lista + aggiorna alert e pulsanti ─────────────
    function renderList() {
        fileList.innerHTML = '';
        const count = selectedFiles.length;

        if (!count) {
            fileList.classList.add('hidden');
            addMoreWrap.classList.add('hidden');
            alertMin.classList.add('hidden');
            submitBtn.disabled = true;
            return;
        }

        fileList.classList.remove('hidden');
        addMoreWrap.classList.remove('hidden');
        // Alert solo in modalità normale con esattamente 1 file
        alertMin.classList.toggle('hidden', isAggiungi || count !== 1);

        selectedFiles.forEach((f, i) => {
            const li = document.createElement('li');
            li.className = 'flex items-center gap-3 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2 text-sm';
            li.innerHTML = `
                <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5
                             7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504
                             -1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504
                             1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
                <span class="truncate text-gray-700 flex-1">${f.name}</span>
                <span class="text-xs text-gray-400 flex-shrink-0 mr-1">${(f.size / 1024 / 1024).toFixed(1)} MB</span>
                <button type="button" data-idx="${i}" title="Rimuovi"
                        class="remove-btn flex-shrink-0 text-gray-400 hover:text-red-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            `;
            fileList.appendChild(li);
        });

        fileList.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', () => removeFile(parseInt(btn.dataset.idx)));
        });

        submitBtn.disabled = isAggiungi ? count < 1 : count < 2;
    }

    // ── Selezione iniziale via "Sfoglia" ────────────────────────
    browseBtn.addEventListener('click', e => {
        e.stopPropagation();
        input.click();
    });
    input.addEventListener('change', () => {
        addFiles(Array.from(input.files));
    });

    // ── Pulsante "Aggiungi altri file" ──────────────────────────
    addMoreBtn.addEventListener('click', () => addMoreInput.click());
    addMoreInput.addEventListener('change', () => {
        addFiles(Array.from(addMoreInput.files).filter(f => f.type === 'application/pdf'));
        addMoreInput.value = '';
    });

    // ── Drag & drop (accumula, non sostituisce) ─────────────────
    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.classList.add('bg-blue-50', 'border-blue-400');
    });
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('bg-blue-50', 'border-blue-400');
    });
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('bg-blue-50', 'border-blue-400');
        const dropped = Array.from(e.dataTransfer.files).filter(f => f.type === 'application/pdf');
        if (dropped.length) addFiles(dropped);
    });

    // ── Submit: ricostruisce DataTransfer da selectedFiles ──────
    form.addEventListener('submit', e => {
        const minRequired = isAggiungi ? 1 : 2;
        if (selectedFiles.length < minRequired) { e.preventDefault(); return; }

        // Ricostruisce input.files da selectedFiles prima dell'invio
        const dt = new DataTransfer();
        selectedFiles.forEach(f => dt.items.add(f));
        input.files = dt.files;

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
