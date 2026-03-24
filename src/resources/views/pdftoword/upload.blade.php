<x-gespidieffe::layouts.app breadcrumb="PDF to Word">

{{-- =========================================================
     GESPIDIEFFE – PDF to Word  |  Step 1: Upload / Step 2: Conferma
     ========================================================= --}}

{{-- Colore tematico: teal  → hex: #0d9488 (500), #0f766e (600), #115e59 (700)
     #f0fdfa (50), #ccfbf1 (100), #99f6e4 (200)
     Non disponibile nel CDN Tailwind 2.x → tutto inline --}}

@if(isset($file))
{{-- ══════════════════════════════════════════════════════
     STEP 2 — Toolbar operativa + conferma
     ══════════════════════════════════════════════════════ --}}

{{-- ── TOOLBAR ──────────────────────────────────────────── --}}
<div class="flex items-center gap-3 px-5 py-3 bg-white border-b border-gray-200 shadow-sm flex-shrink-0 flex-wrap">

    <div class="flex items-center gap-2 mr-2">
        <svg class="w-5 h-5" style="color:#0d9488" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5
                     7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5
                     2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125
                     1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
        </svg>
        <span class="font-bold text-gray-800 text-sm">PDF to Word</span>
    </div>

    <div class="h-6 w-px bg-gray-300 mx-1 ml-auto"></div>

    {{-- Bottone Avvia conversione --}}
    <button id="convertBtn"
            onclick="avviaConversione()"
            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white
                   transition-colors shadow-sm focus:outline-none"
            style="background:#0d9488"
            onmouseenter="if(!this.disabled) this.style.background='#0f766e'"
            onmouseleave="if(!this.disabled) this.style.background='#0d9488'">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
        Avvia conversione
    </button>

    {{-- Bottone Cambia file --}}
    <a href="{{ route('gespidieffe.pdf2word') }}"
       class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-gray-600
              bg-gray-100 hover:bg-gray-200 transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181
                     3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181
                     3.182m0-4.991v4.99" />
        </svg>
        <span id="cambiaFileLbl">Cambia file</span>
    </a>

    {{-- Progress inline nella toolbar --}}
    <div id="progressWrap" class="hidden flex items-center gap-3 ml-2">
        <div class="w-40 bg-gray-200 rounded-full h-2">
            <div id="progressBar" class="h-2 rounded-full transition-all duration-500"
                 style="width:0%; background:#0d9488"></div>
        </div>
        <p id="progressText" class="text-xs text-gray-500 whitespace-nowrap">
            Conversione in corso…
        </p>
    </div>

</div>

{{-- ── Contenuto step 2 ─────────────────────────────────── --}}
<div class="flex flex-col w-full min-h-screen bg-gray-50">

    {{-- Titolo --}}
    <div class="px-8 pt-6 pb-2 text-center">
        <div class="flex items-center justify-center gap-3 mb-1">
            <svg class="w-7 h-7" style="color:#0d9488" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5
                         7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5
                         2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125
                         1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">PDF to Word</h1>
        </div>
        <p class="text-sm text-gray-500 mt-1">Converti un file PDF in documento Word (.docx) editabile</p>
    </div>

    {{-- Card file — senza pulsanti --}}
    <div class="flex justify-center px-4 pt-3">
        <div class="w-full max-w-xl">
            <div class="flex items-center gap-4 p-4 rounded-xl bg-white shadow-sm"
                 style="border:1px solid #99f6e4">
                <svg class="w-10 h-10 flex-shrink-0" style="color:#0d9488" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5
                             7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504
                             -1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504
                             1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $original ?? 'documento.pdf' }}</p>
                    <p class="text-xs mt-0.5"
                       style="color: {{ $tipo === 'nativo' ? '#0f766e' : ($tipo === 'ibrido' ? '#1d4ed8' : '#b45309') }}">
                        @if($tipo === 'nativo')
                            PDF nativo — conversione diretta con pdf2docx (layout preservato)
                        @elseif($tipo === 'ibrido')
                            PDF ibrido — testo OCR già presente, estratto con pdftotext
                        @else
                            PDF scansionato — verrà applicato OCR (Tesseract, lingua: ita+eng)
                        @endif
                    </p>
                </div>
                <span class="flex-shrink-0 text-xs font-bold px-2 py-1 rounded-full"
                      style="{{ $tipo === 'nativo'
                        ? 'background:#ccfbf1; color:#0f766e'
                        : ($tipo === 'ibrido'
                            ? 'background:#dbeafe; color:#1d4ed8'
                            : 'background:#fef3c7; color:#92400e') }}">
                    {{ $tipo === 'nativo' ? 'NATIVO' : ($tipo === 'ibrido' ? 'IBRIDO' : 'SCANSIONATO') }}
                </span>
            </div>

            {{-- Risultato download --}}
            <div id="downloadWrap" class="hidden mt-4 p-4 rounded-xl text-center"
                 style="background:#f0fdfa; border:1px solid #99f6e4">
                <p class="text-sm font-semibold mb-3" style="color:#0f766e">
                    Conversione completata!
                </p>
                <a id="downloadLink" href="#"
                   class="inline-flex items-center gap-2 font-semibold px-5 py-2.5 rounded-lg text-white text-sm"
                   style="background:#0d9488"
                   onmouseenter="this.style.background='#0f766e'"
                   onmouseleave="this.style.background='#0d9488'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021
                                 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Scarica .docx
                </a>
            </div>

            {{-- Errore conversione --}}
            <div id="errorWrap" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded-xl text-center">
                <p class="text-sm font-semibold text-red-700" id="errorMsg">
                    Conversione fallita.
                </p>
                <a href="{{ route('gespidieffe.pdf2word') }}"
                   class="mt-2 inline-block text-xs text-red-500 hover:text-red-700 underline">
                    Riprova
                </a>
            </div>

        </div>
    </div>

</div>

@push('scripts')
<script>
(function () {
    const fileUuid    = @json($file);
    const csrfToken   = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const convertBtn  = document.getElementById('convertBtn');
    const progressWrap= document.getElementById('progressWrap');
    const progressBar = document.getElementById('progressBar');
    const progressText= document.getElementById('progressText');
    const downloadWrap= document.getElementById('downloadWrap');
    const downloadLink= document.getElementById('downloadLink');
    const errorWrap   = document.getElementById('errorWrap');
    const errorMsg    = document.getElementById('errorMsg');

    window.avviaConversione = function () {
        convertBtn.disabled = true;
        convertBtn.style.background = '#d1d5db';
        progressWrap.classList.remove('hidden');

        let pct = 0;
        const iv = setInterval(() => {
            pct = Math.min(pct + Math.random() * 8, 88);
            progressBar.style.width = pct + '%';
        }, 600);

        fetch('{{ route('gespidieffe.pdf2word.applica') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ file: fileUuid }),
        })
        .then(r => r.json())
        .then(data => {
            clearInterval(iv);
            progressBar.style.width = '100%';
            if (data.download_token) {
                downloadLink.href = '{{ url('gespidieffe/pdf2word/download') }}/' + data.download_token;
                setTimeout(() => {
                    progressWrap.classList.add('hidden');
                    convertBtn.classList.add('hidden');
                    downloadWrap.classList.remove('hidden');
                    document.getElementById('cambiaFileLbl').textContent = 'Converti un altro file';
                }, 400);
            } else {
                throw new Error(data.message ?? 'Errore sconosciuto');
            }
        })
        .catch(err => {
            clearInterval(iv);
            progressWrap.classList.add('hidden');
            convertBtn.disabled = false;
            convertBtn.style.background = '#0d9488';
            errorMsg.textContent = 'Conversione fallita: ' + (err.message ?? 'errore sconosciuto');
            errorWrap.classList.remove('hidden');
        });
    };

    let _skip = false;
    downloadLink.addEventListener('click', () => { _skip = true; });
    window.addEventListener('beforeunload', () => {
        if (_skip) return;
        navigator.sendBeacon(
            '{{ route('gespidieffe.pdf2word.elimina', ['file' => '__UUID__']) }}'.replace('__UUID__', fileUuid),
            new Blob([JSON.stringify({ _token: csrfToken })], { type: 'application/json' })
        );
    });
})();
</script>
@endpush

@else
{{-- ══════════════════════════════════════════════════════
     STEP 1 — Upload file
     ══════════════════════════════════════════════════════ --}}

<div class="flex flex-col w-full min-h-screen bg-gray-50">

    {{-- Titolo --}}
    <div class="px-8 pt-8 pb-4 text-center">
        <div class="flex items-center justify-center gap-3 mb-1">
            <svg class="w-7 h-7" style="color:#0d9488" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5
                         7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5
                         2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125
                         1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">PDF to Word</h1>
        </div>
        <p class="text-sm text-gray-500 mt-1">Converti un file PDF in documento Word (.docx) editabile</p>
    </div>

    {{-- Area centrale --}}
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

            <div class="bg-white rounded-2xl shadow-md p-8">
                <form method="POST"
                      action="{{ route('gespidieffe.pdf2word.upload') }}"
                      enctype="multipart/form-data"
                      id="uploadForm">
                    @csrf

                    {{-- Drop zone --}}
                    <div id="dropZone"
                         class="relative flex flex-col items-center justify-center
                                border-2 border-dashed border-gray-300 rounded-xl
                                bg-gray-50 transition-colors duration-200
                                p-10 text-center cursor-pointer">

                        <svg class="w-14 h-14 mb-4" style="color:#0d9488" fill="none" viewBox="0 0 24 24"
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
                                class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-colors shadow-sm"
                                style="background:#0d9488"
                                onmouseenter="this.style.background='#0f766e'"
                                onmouseleave="this.style.background='#0d9488'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                            </svg>
                            Sfoglia file…
                        </button>
                    </div>

                    {{-- File selezionato --}}
                    <div id="fileInfo" class="hidden mt-4 flex items-center gap-3 rounded-lg px-4 py-3"
                         style="background:#f0fdfa; border:1px solid #99f6e4">
                        <svg class="w-5 h-5 flex-shrink-0" style="color:#0d9488" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5
                                     7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504
                                     -1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504
                                     1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                        <span id="fileName" class="truncate text-sm text-gray-700 flex-1"></span>
                        <span id="fileSize" class="text-xs text-gray-400 flex-shrink-0"></span>
                    </div>

                    <p class="text-xs text-gray-400 text-center mt-3">Max 100 MB</p>

                    {{-- Progress upload --}}
                    <div id="progressWrap" class="hidden mt-5">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progressBar" class="h-2 rounded-full transition-all duration-300"
                                 style="width:0%; background:#0d9488"></div>
                        </div>
                        <p id="progressText" class="text-xs text-gray-500 text-center mt-1">Caricamento…</p>
                    </div>

                    {{-- Bottone --}}
                    <button type="submit"
                            id="submitBtn"
                            disabled
                            class="mt-6 w-full flex items-center justify-center gap-2
                                   font-semibold py-3 rounded-xl text-white
                                   transition-colors duration-200 focus:outline-none"
                            style="background:#d1d5db">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021
                                     18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                        Carica e converti
                    </button>

                </form>
            </div>

            {{-- Info box --}}
            <div class="mt-8 grid grid-cols-2 gap-4 text-sm text-gray-600">
                <div class="flex gap-3 items-start bg-white rounded-xl p-4 shadow-sm">
                    <span class="text-2xl">📄</span>
                    <div>
                        <p class="font-semibold text-gray-800">PDF nativo</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Testo selezionabile → conversione diretta con pdf2docx, layout preservato
                        </p>
                    </div>
                </div>
                <div class="flex gap-3 items-start bg-white rounded-xl p-4 shadow-sm">
                    <span class="text-2xl">🔍</span>
                    <div>
                        <p class="font-semibold text-gray-800">PDF scansionato</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Solo immagini → OCR automatico con Tesseract (ita+eng) per estrarre il testo
                        </p>
                    </div>
                </div>
                <div class="flex gap-3 items-start bg-white rounded-xl p-4 shadow-sm col-span-2">
                    <span class="text-2xl">⚡</span>
                    <div>
                        <p class="font-semibold text-gray-800">Rilevamento automatico</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Il sistema rileva automaticamente se il PDF è nativo o scansionato e applica
                            la strategia di conversione ottimale.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
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

    browseBtn.addEventListener('click', e => { e.stopPropagation(); input.click(); });

    function showFile(file) {
        if (!file || file.type !== 'application/pdf') {
            fileInfo.classList.add('hidden');
            submitBtn.disabled = true;
            submitBtn.style.background = '#d1d5db';
            return;
        }
        fileName.textContent = file.name;
        fileSize.textContent = (file.size / 1024 / 1024).toFixed(1) + ' MB';
        fileInfo.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.style.background = '#0d9488';
    }

    input.addEventListener('change', () => showFile(input.files[0]));

    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.style.background = '#f0fdfa';
        dropZone.style.borderColor = '#0d9488';
    });
    dropZone.addEventListener('dragleave', () => {
        dropZone.style.background = '';
        dropZone.style.borderColor = '';
    });
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.style.background = '';
        dropZone.style.borderColor = '';
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
        submitBtn.style.background = '#d1d5db';
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
@endpush

@endif

</x-gespidieffe::layouts.app>