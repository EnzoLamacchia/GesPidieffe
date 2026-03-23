<x-gespidieffe::layouts.app>

<div class="flex flex-col w-full min-h-screen bg-gray-50">

    {{-- ── Titolo pagina ───────────────────────────────────────── --}}
    <div class="px-8 py-10 text-center">
        <div class="flex items-center justify-center gap-3 mb-1">
            <img src="/vendor/dedir/assets/materialDashboard/img/devEL-logo120trasp.png" alt="devEL" class="w-20 h-20 flex-shrink-0">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">GesPidieffe</h1>
        </div>
        <p class="text-sm text-gray-500 mt-1">Gestione e modifica di documenti PDF</p>
    </div>

    {{-- ── Griglia funzioni ────────────────────────────────────── --}}
    <div class="flex-1 px-8 pb-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto">

            {{-- CENSURA PDF --}}
            <a href="{{ route('gespidieffe.censura') }}"
               class="group flex flex-col bg-white rounded-2xl shadow-sm border border-gray-200
                      hover:shadow-lg hover:border-red-300 transition-all duration-200 overflow-hidden">
                <div class="flex items-center justify-center h-28 bg-gradient-to-br from-red-50 to-red-100
                            group-hover:from-red-100 group-hover:to-red-200 transition-all duration-200">
                    <svg class="w-12 h-12 text-red-500" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993
                                 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773
                                 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228
                                 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0
                                 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </div>
                <div class="p-4 flex-1">
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Censura PDF</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Oscura parti sensibili con rettangoli neri o bianchi. Il testo censurato è irrecuperabile.
                    </p>
                </div>
                <div class="px-4 pb-3">
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-red-600">
                        Apri <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </span>
                </div>
            </a>

            {{-- MERGE PDF --}}
            <a href="{{ route('gespidieffe.merge') }}"
               class="group flex flex-col bg-white rounded-2xl shadow-sm border border-gray-200
                      hover:shadow-lg hover:border-blue-300 transition-all duration-200 overflow-hidden">
                <div class="flex items-center justify-center h-28 bg-gradient-to-br from-blue-50 to-blue-100
                            group-hover:from-blue-100 group-hover:to-blue-200 transition-all duration-200">
                    <svg class="w-12 h-12 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                    </svg>
                </div>
                <div class="p-4 flex-1">
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Merge PDF</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Unisci più file PDF in un unico documento, scegliendo l'ordine dei file.
                    </p>
                </div>
                <div class="px-4 pb-3">
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600">
                        Apri <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </span>
                </div>
            </a>

            {{-- SPLIT PDF --}}
            <a href="{{ route('gespidieffe.split') }}"
               class="group flex flex-col bg-white rounded-2xl shadow-sm border border-gray-200
                      hover:shadow-lg hover:border-purple-300 transition-all duration-200 overflow-hidden">
                <div class="flex items-center justify-center h-28 bg-gradient-to-br from-purple-50 to-purple-100
                            group-hover:from-purple-100 group-hover:to-purple-200 transition-all duration-200">
                    <svg class="w-12 h-12 text-purple-500" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25
                                 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                    </svg>
                </div>
                <div class="p-4 flex-1">
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Split PDF</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Dividi un PDF in più file separati, per pagina singola o per intervalli personalizzati.
                    </p>
                </div>
                <div class="px-4 pb-3">
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-purple-600">
                        Apri <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </span>
                </div>
            </a>

            {{-- ORGANIZZA PAGINE --}}
            <a href="{{ route('gespidieffe.organizza') }}"
               class="group flex flex-col bg-white rounded-2xl shadow-sm border border-gray-200
                      hover:shadow-lg hover:border-yellow-300 transition-all duration-200 overflow-hidden">
                <div class="flex items-center justify-center h-28 bg-gradient-to-br from-yellow-50 to-yellow-100
                            group-hover:from-yellow-100 group-hover:to-yellow-200 transition-all duration-200">
                    <svg class="w-12 h-12 text-yellow-500" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0
                                 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016
                                 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25
                                 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0
                                 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6z
                                 M13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25
                                 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                </div>
                <div class="p-4 flex-1">
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Organizza pagine</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Riordina, elimina o duplica le pagine di un PDF trascinandole nella posizione desiderata.
                    </p>
                </div>
                <div class="px-4 pb-3">
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-yellow-600">
                        Apri <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </span>
                </div>
            </a>

            {{-- RUOTA PAGINE --}}
            <a href="{{ route('gespidieffe.ruota') }}"
               class="group flex flex-col bg-white rounded-2xl shadow-sm border border-gray-200
                      hover:shadow-lg hover:border-green-300 transition-all duration-200 overflow-hidden">
                <div class="flex items-center justify-center h-28 bg-gradient-to-br from-green-50 to-green-100
                            group-hover:from-green-100 group-hover:to-green-200 transition-all duration-200">
                    <svg class="w-12 h-12 text-green-500" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181
                                 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181
                                 3.182m0-4.991v4.99" />
                    </svg>
                </div>
                <div class="p-4 flex-1">
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Ruota pagine</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Ruota singole pagine o l'intero documento di 90°, 180° o 270°.
                    </p>
                </div>
                <div class="px-4 pb-3">
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-600">
                        Apri <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </span>
                </div>
            </a>

            {{-- NUMERA PAGINE --}}
            <a href="{{ route('gespidieffe.numera') }}"
               class="group flex flex-col bg-white rounded-2xl shadow-sm border border-gray-200
                      hover:shadow-lg hover:border-indigo-300 transition-all duration-200 overflow-hidden">
                <div class="flex items-center justify-center h-28 bg-gradient-to-br from-indigo-50 to-indigo-100
                            group-hover:from-indigo-100 group-hover:to-indigo-200 transition-all duration-200">
                    <svg class="w-12 h-12 text-indigo-500" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5l-3.9 19.5m-2.1-19.5l-3.9 19.5" />
                    </svg>
                </div>
                <div class="p-4 flex-1">
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Numera pagine</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Aggiungi numeri di pagina: scegli formato, posizione e pagina iniziale. Testo originale preservato.
                    </p>
                </div>
                <div class="px-4 pb-3">
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600">
                        Apri <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </span>
                </div>
            </a>

            {{-- UNISCI E ORGANIZZA --}}
            <a href="{{ route('gespidieffe.unisciorganizza') }}"
               class="group flex flex-col bg-white rounded-2xl shadow-sm border border-gray-200
                      hover:shadow-lg transition-all duration-200 overflow-hidden"
               onmouseenter="this.style.borderColor='#fdba74'"
               onmouseleave="this.style.borderColor=''">
                <div class="flex items-center justify-center h-28"
                     style="background: linear-gradient(135deg, #fff7ed, #fed7aa);">
                    <svg class="w-12 h-12" style="color:#f97316" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6 6.878V6a2.25 2.25 0 012.25-2.25h7.5A2.25 2.25 0 0118 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 004.5 9v.878m13.5-3A2.25 2.25 0 0119.5 9v.878m0 0a2.246 2.246 0 00-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0121 12v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6c0-.98.626-1.813 1.5-2.122" />
                    </svg>
                </div>
                <div class="p-4 flex-1">
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Unisci e Organizza</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Unisci più PDF in un documento, poi riorganizza liberamente le singole pagine del risultato.
                    </p>
                </div>
                <div class="px-4 pb-3">
                    <span class="inline-flex items-center gap-1 text-xs font-semibold" style="color:#ea580c">
                        Apri <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </span>
                </div>
            </a>

        </div>
    </div>
</div>

</x-gespidieffe::layouts.app>