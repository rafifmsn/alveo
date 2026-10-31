@props(['title' => 'Dashboard'])
@php
    $currentUser = auth()->user();
    $isDark = $currentUser?->dark_mode ?? false;
    $initials = collect(explode(' ', $currentUser?->name ?? 'Athlete'))
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->take(2)
        ->implode('');

    $navItems = [
        ['label' => 'Window Finder', 'route' => 'finder', 'icon' => 'compass', 'active' => request()->routeIs('finder*')],
        ['label' => 'Saved Routes', 'route' => 'routes.index', 'icon' => 'route', 'active' => request()->routeIs('routes.*')],
        ['label' => 'Inhaled Dosimetry', 'route' => 'dosimetry.index', 'icon' => 'activity', 'active' => request()->routeIs('dosimetry.*')],
        ['label' => 'Exposure History', 'route' => 'history.index', 'icon' => 'history', 'active' => request()->routeIs('history.*')],
        ['label' => 'Club Broadcasts', 'route' => 'broadcasts.index', 'icon' => 'megaphone', 'active' => request()->routeIs('broadcasts.*')],
        ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'sliders', 'active' => request()->routeIs('settings.*')],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{
          darkMode: localStorage.getItem('alveo_dark') !== null ? localStorage.getItem('alveo_dark') === 'true' : {{ $isDark ? 'true' : 'false' }},
          mobileNavOpen: false,
          toggleTheme() {
              this.darkMode = !this.darkMode;
              localStorage.setItem('alveo_dark', this.darkMode);
          }
      }"
      :data-theme="darkMode ? 'alveo-dark' : 'alveo'"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - Alveo</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300..800;1,9..40,300..800&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.14/dist/full.min.css" rel="stylesheet" type="text/css" />
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    fontFamily: {
                        sans: ['"DM Sans"', 'system-ui', '-apple-system', 'sans-serif'],
                        serif: ['"DM Sans"', 'system-ui', '-apple-system', 'sans-serif'],
                        mono: ['"DM Sans"', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    extend: {
                        colors: {
                            forest: {
                                50: '#ecfdf5',
                                100: '#d1fae5',
                                200: '#a7f3d0',
                                300: '#6ee7b7',
                                400: '#34d399',
                                500: '#10b981',
                                600: '#059669',
                                700: '#047857',
                                800: '#065f46',
                                900: '#064e3b',
                                950: '#022c22',
                            }
                        }
                    }
                }
            };
        </script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    @endif

    <style>
        html, body, button, input, select, textarea, code, pre, kbd, samp {
            font-family: 'DM Sans', system-ui, -apple-system, BlinkMacSystemFont, sans-serif !important;
        }
        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }
        [x-cloak] { display: none !important; }

        /* Dark / Light Mode SVG Logo Visibility */
        html.dark .logo-dark,
        html[data-theme="alveo-dark"] .logo-dark {
            display: block !important;
        }
        html.dark .logo-light,
        html[data-theme="alveo-dark"] .logo-light {
            display: none !important;
        }
        html:not(.dark) .logo-dark,
        html[data-theme="alveo"] .logo-dark {
            display: none !important;
        }
        html:not(.dark) .logo-light,
        html[data-theme="alveo"] .logo-light {
            display: block !important;
        }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        .leaflet-container { font-family: inherit !important; }
        .leaflet-pane { z-index: 10 !important; }
        .leaflet-top, .leaflet-bottom { z-index: 15 !important; }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50/70 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased min-h-screen selection:bg-forest-800 selection:text-white">

    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Sidebar Navigation -->
        <aside class="w-full lg:w-64 lg:min-h-screen bg-white dark:bg-slate-900 border-b lg:border-b-0 lg:border-r border-slate-200/90 dark:border-slate-800 flex flex-col justify-between shrink-0">
            <div>
                <!-- Brand Header: Alveo SVG Logo -->
                <div class="h-20 px-7 flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800">
                    <a href="{{ route('finder') }}" class="flex items-center">
                        <img src="{{ asset('images/alveo-dark.svg') }}"
                             alt="Alveo"
                             class="h-8 w-auto logo-dark"
                             :class="{ 'hidden': !darkMode, 'block': darkMode }" />
                        <img src="{{ asset('images/alveo-light.svg') }}"
                             alt="Alveo"
                             class="h-8 w-auto logo-light"
                             :class="{ 'block': !darkMode, 'hidden': darkMode }" />
                    </a>

                    <button type="button"
                            @click="mobileNavOpen = !mobileNavOpen"
                            class="lg:hidden p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                        <span class="text-sm font-semibold">Menu</span>
                    </button>
                </div>

                <!-- Navigation Links -->
                <nav class="p-4 space-y-1.5" :class="{ 'hidden lg:block': !mobileNavOpen, 'block': mobileNavOpen }">
                    @foreach ($navItems as $item)
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-150 {{ $item['active']
                                ? 'bg-forest-900 text-white dark:bg-forest-700 dark:text-white shadow-xs font-semibold'
                                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-white' }}">
                            <x-icon :name="$item['icon']" class="w-5 h-5 shrink-0 {{ $item['active'] ? 'text-emerald-300' : 'text-slate-400 dark:text-slate-500' }}" />
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>

            <!-- User Footer -->
            <div class="p-5 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-full bg-forest-100 dark:bg-forest-900/70 border border-forest-300 dark:border-forest-700 flex items-center justify-center text-forest-900 dark:text-forest-200 font-bold text-sm shrink-0">
                        {{ $initials }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">
                            {{ $currentUser?->name ?? 'Rafif Muchsin' }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                            {{ ucfirst($currentUser?->sensitivity_profile ?? 'Standard') }} Threshold
                        </p>
                    </div>
                </div>

                <button type="button"
                        @click="toggleTheme()"
                        title="Toggle Light / Dark Theme"
                        class="p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white transition">
                    <x-icon name="sun" x-show="darkMode" x-cloak class="w-4 h-4 text-amber-400" />
                    <x-icon name="moon" x-show="!darkMode" class="w-4 h-4" />
                </button>
            </div>
        </aside>

        <!-- Main Workspace -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Bar -->
            <header class="h-20 px-6 lg:px-10 bg-white dark:bg-slate-900 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                        {{ $title }}
                    </h1>
                </div>
            </header>

            <!-- Flash Status Banner -->
            @if (session('status'))
                <div class="px-6 lg:px-10 pt-6">
                    <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-forest-50 dark:bg-forest-950/90 border border-forest-200 dark:border-forest-800 text-forest-900 dark:text-forest-200 text-sm font-medium">
                        <x-icon name="check-circle" class="w-5 h-5 text-forest-700 dark:text-forest-400 shrink-0" />
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="px-6 lg:px-10 pt-6">
                    <div class="px-4 py-3 rounded-xl bg-red-50 dark:bg-red-950/60 border border-red-200 dark:border-red-800/70 text-red-800 dark:text-red-200 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Page Content -->
            <main class="flex-1 p-6 lg:p-10 max-w-7xl w-full">
                {{ $slot }}
            </main>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @stack('scripts')
</body>
</html>

