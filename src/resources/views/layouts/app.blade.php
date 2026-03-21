<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="/assets/img/brainOrange.png" />
    <title>GesPidieffe – {{ config('app.name', 'NAPP') }}</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">
    <link rel="stylesheet" href="https://unpkg.com/tailwindcss@2.2.19/dist/tailwind.min.css"/>

    @vite('resources/js/app.js')
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50 min-h-screen flex flex-col">

    {{-- ── Top navbar ──────────────────────────────────────────── --}}
    <nav class="bg-white border-b border-gray-200 shadow-sm flex-shrink-0">
        <div class="flex items-center justify-between px-6 py-3">

            {{-- Sinistra: logo + titolo --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('gespidieffe.home') }}" class="flex items-center gap-2 group">
                    <img src="/vendor/dedir/assets/materialDashboard/img/devEL-logo120trasp.png" alt="devEL" class="w-16 h-16 flex-shrink-0">
                    <span class="text-lg font-bold text-gray-800 group-hover:text-red-600 transition-colors">GesPidieffe</span>
                </a>

                {{-- Breadcrumb --}}
                @isset($breadcrumb)
                    <span class="text-gray-300 mx-1">›</span>
                    <span class="text-sm text-gray-500">{{ $breadcrumb }}</span>
                @endisset
            </div>

            {{-- Destra: dropdown Funzioni + utente + torna alla dashboard --}}
            <div class="flex items-center gap-4">

                {{-- Dropdown Funzioni --}}
                <div class="relative" x-data="{ open: false }" @@click.outside="open = false">
                    <button @@click="open = !open"
                            class="flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900 font-medium transition-colors mr-6">
                        Funzioni
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>
                    <div x-show="open" x-transition
                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                        <a href="{{ route('gespidieffe.censura') }}"   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><span class="text-red-500">●</span> Censura PDF</a>
                        <a href="{{ route('gespidieffe.merge') }}"     class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><span class="text-blue-500">●</span> Merge PDF</a>
                        <a href="{{ route('gespidieffe.split') }}"     class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><span class="text-green-500">●</span> Split PDF</a>
                        <a href="{{ route('gespidieffe.organizza') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><span class="text-purple-500">●</span> Organizza pagine</a>
                        <a href="{{ route('gespidieffe.ruota') }}"     class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><span class="text-yellow-500">●</span> Ruota pagine</a>
                        <a href="{{ route('gespidieffe.numera') }}"    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><span class="text-pink-500">●</span> Numera pagine</a>
                    </div>
                </div>

                @auth
                    <span class="text-sm text-gray-500 hidden sm:inline">
                        {{ auth()->user()->name }} {{ auth()->user()->surname ?? '' }}
                    </span>
                @endauth
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                    Dashboard
                </a>
            </div>

        </div>
    </nav>

    {{-- ── Contenuto pagina ────────────────────────────────────── --}}
    <main class="flex-1 flex flex-col">
        {{ $slot }}
    </main>

    @stack('scripts')
</body>
</html>
