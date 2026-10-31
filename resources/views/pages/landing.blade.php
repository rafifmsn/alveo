<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Alveo - Aerobic Clean-Air Window & Pulmonary Exposure Engine</title>

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
    @endif

    <style>
        html, body, button, input, select, textarea, code, pre, kbd, samp {
            font-family: 'DM Sans', system-ui, -apple-system, BlinkMacSystemFont, sans-serif !important;
        }
        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }
    </style>
</head>
<body class="bg-white text-slate-900 antialiased selection:bg-forest-900 selection:text-white">

    <!-- Top Single-Page Header: Plain Text Logo Left, Single CTA Right (No Hamburger Button) -->
    <header class="w-full border-b border-slate-100 bg-white/95 backdrop-blur-xs sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="text-2xl font-bold tracking-tight text-slate-900">
                Alveo
            </a>

            @auth
                <a href="{{ route('finder') }}"
                   class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-slate-100 hover:bg-forest-900 text-slate-900 hover:text-white border border-slate-200/80 transition">
                    Open Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-slate-100 hover:bg-forest-900 text-slate-900 hover:text-white border border-slate-200/80 transition">
                    Get Started
                </a>
            @endauth
        </div>
    </header>

    <main>
        <!-- Section 1: Hero Section & Large Screen Preview -->
        <section class="pt-16 sm:pt-20 pb-16 px-6">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-slate-900 leading-[1.12]">
                    Train Smarter<br>
                    with Clean-Air Window Insights
                </h1>

                <p class="mt-5 text-base sm:text-lg text-slate-500 max-w-2xl mx-auto leading-relaxed">
                    Alveo helps runners, cyclists, and endurance clubs time outdoor sessions around pulmonary exposure, wind dispersion, and hourly PM2.5 troughs.
                </p>

                <div class="mt-8">
                    @auth
                        <a href="{{ route('finder') }}"
                           class="inline-flex items-center justify-center px-6 py-3 rounded-xl text-sm font-semibold bg-forest-900 hover:bg-forest-800 text-white shadow-sm transition">
                            Open Window Finder
                        </a>
                    @else
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center justify-center px-6 py-3 rounded-xl text-sm font-semibold bg-forest-900 hover:bg-forest-800 text-white shadow-sm transition">
                            Start Using Alveo Free
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Large Hero Screen Preview Container -->
            <div class="max-w-5xl mx-auto mt-12 sm:mt-14">
                <div class="rounded-2xl sm:rounded-3xl p-2 sm:p-3 bg-slate-100/80 border border-slate-200/90 shadow-sm overflow-hidden">
                    <div class="rounded-xl sm:rounded-2xl overflow-hidden bg-white border border-slate-200/80 aspect-16/10">
                        <img src="{{ asset('images/landing/hero-dashboard.svg') }}"
                             alt="Alveo Dashboard Window Finder Preview"
                             class="w-full h-full object-cover object-top" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 2: Trusted Strip & 2-Column Split Feature -->
        <section class="py-16 sm:py-24 px-6 border-t border-slate-100">
            <div class="max-w-6xl mx-auto">
                <!-- Trusted Strip -->
                <div class="text-center">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        Monitored across Greater Jakarta endurance corridors
                    </p>

                    <div class="mt-6 flex flex-wrap items-center justify-center gap-x-10 gap-y-4 text-slate-700">
                        <span class="text-base sm:text-lg font-bold tracking-tight text-slate-800">Senayan Loop</span>
                        <span class="text-base sm:text-lg font-bold tracking-tight text-slate-800">Sudirman Peloton</span>
                        <span class="text-base sm:text-lg font-bold tracking-tight text-slate-800">PIK Coastal</span>
                        <span class="text-base sm:text-lg font-bold tracking-tight text-slate-800">BSD Boulevard</span>
                        <span class="text-base sm:text-lg font-bold tracking-tight text-slate-800">UI Forest Trail</span>
                    </div>
                </div>

                <!-- 2-Column Split Content -->
                <div class="mt-16 sm:mt-20 grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                    <!-- Left Visual Placeholder -->
                    <div class="lg:col-span-6">
                        <div class="rounded-3xl overflow-hidden bg-slate-50 border border-slate-200/80 aspect-6/5 shadow-2xs">
                            <img src="{{ asset('images/landing/feature-workflow.svg') }}"
                                 alt="Alveo Corridor Dispersion Workflow"
                                 class="w-full h-full object-cover" />
                        </div>
                    </div>

                    <!-- Right Copy & 4 Feature Points -->
                    <div class="lg:col-span-6 space-y-6">
                        <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-slate-900 leading-tight">
                            Step Into Deterministic Exposure Planning.
                        </h2>

                        <div class="space-y-4 text-sm sm:text-base text-slate-500 leading-relaxed">
                            <p>
                                Static city-wide AQI readings assume resting respiration of 8 liters per minute. Endurance athletes in Zone 3 and Zone 4 ventilate 45 to 85 liters per minute, bypassing nasal filtration and driving fine particulates deep into alveolar tissue.
                            </p>
                            <p>
                                Alveo evaluates 3-day hourly PM2.5, surface wind speed, and ultraviolet curves so you can shift your session into the cleanest boundary-layer window.
                            </p>
                        </div>

                        <!-- 2x2 Feature Checklist -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-1">
                            <div class="flex items-center gap-2.5 text-sm font-semibold text-slate-800">
                                <span class="w-6 h-6 rounded-lg bg-forest-50 border border-forest-200 flex items-center justify-center text-forest-800 shrink-0">
                                    <x-icon name="activity" class="w-3.5 h-3.5" />
                                </span>
                                <span>3.5x Ventilation Multiplier</span>
                            </div>

                            <div class="flex items-center gap-2.5 text-sm font-semibold text-slate-800">
                                <span class="w-6 h-6 rounded-lg bg-forest-50 border border-forest-200 flex items-center justify-center text-forest-800 shrink-0">
                                    <x-icon name="wind" class="w-3.5 h-3.5" />
                                </span>
                                <span>Hourly Dispersion Slicing</span>
                            </div>

                            <div class="flex items-center gap-2.5 text-sm font-semibold text-slate-800">
                                <span class="w-6 h-6 rounded-lg bg-forest-50 border border-forest-200 flex items-center justify-center text-forest-800 shrink-0">
                                    <x-icon name="database" class="w-3.5 h-3.5" />
                                </span>
                                <span>Database-First Caching</span>
                            </div>

                            <div class="flex items-center gap-2.5 text-sm font-semibold text-slate-800">
                                <span class="w-6 h-6 rounded-lg bg-forest-50 border border-forest-200 flex items-center justify-center text-forest-800 shrink-0">
                                    <x-icon name="users" class="w-3.5 h-3.5" />
                                </span>
                                <span>Club Broadcast Ready</span>
                            </div>
                        </div>

                        <div class="pt-2">
                            @auth
                                <a href="{{ route('finder') }}"
                                   class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-semibold bg-forest-900 hover:bg-forest-800 text-white transition">
                                    Evaluate a Corridor
                                </a>
                            @else
                                <a href="{{ route('register') }}"
                                   class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-semibold bg-forest-900 hover:bg-forest-800 text-white transition">
                                    Create Free Account
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 3: 3-Column Value Proposition Grid -->
        <section class="py-16 sm:py-24 px-6 border-t border-slate-100">
            <div class="max-w-6xl mx-auto">
                <div class="max-w-2xl mx-auto text-center">
                    <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-slate-900 leading-tight">
                        Where Endurance Training<br>
                        Meets Atmospheric Precision
                    </h2>
                    <p class="mt-3.5 text-sm sm:text-base text-slate-500">
                        Deterministic pulmonary dosimetry that helps you protect aerobic longevity without guesswork.
                    </p>
                </div>

                <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Card 1 -->
                    <div class="rounded-2xl bg-slate-50/80 border border-slate-200/80 p-7 space-y-3">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200/90 flex items-center justify-center text-forest-900 shadow-2xs">
                            <x-icon name="compass" class="w-5 h-5" />
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 pt-1">
                            Understands Your Corridor
                        </h3>
                        <p class="text-sm text-slate-500 leading-relaxed">
                            Monitors saved training loops across Jakarta and Greater Tangerang with automatic 3-hour lazy refresh caching.
                        </p>
                    </div>

                    <!-- Card 2 -->
                    <div class="rounded-2xl bg-slate-50/80 border border-slate-200/80 p-7 space-y-3">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200/90 flex items-center justify-center text-forest-900 shadow-2xs">
                            <x-icon name="heart-pulse" class="w-5 h-5" />
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 pt-1">
                            Deterministic by Design
                        </h3>
                        <p class="text-sm text-slate-500 leading-relaxed">
                            Computes exact inhaled PM2.5 mass in micrograms across Zones 1 to 5 and compares resting vs exertion exposure.
                        </p>
                    </div>

                    <!-- Card 3 -->
                    <div class="rounded-2xl bg-slate-50/80 border border-slate-200/80 p-7 space-y-3">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200/90 flex items-center justify-center text-forest-900 shadow-2xs">
                            <x-icon name="megaphone" class="w-5 h-5" />
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 pt-1">
                            Built for Run & Ride Clubs
                        </h3>
                        <p class="text-sm text-slate-500 leading-relaxed">
                            Publish verified GO or DELAY window dispatches for your squad and let members RSVP with a single click.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 4: 2-Column Visual Feature Cards -->
        <section class="py-16 sm:py-20 px-6">
            <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Visual Card -->
                <div class="rounded-3xl bg-slate-50/70 border border-slate-200/80 overflow-hidden flex flex-col">
                    <div class="aspect-16/8 w-full overflow-hidden bg-slate-100 border-b border-slate-200/60">
                        <img src="{{ asset('images/landing/card-dosimetry.svg') }}"
                             alt="Instant Pulmonary Dosimetry Suggestions"
                             class="w-full h-full object-cover" />
                    </div>
                    <div class="p-7 sm:p-8 space-y-2.5">
                        <h3 class="text-xl font-bold text-slate-900">
                            Instant Dosimetry Guidance
                        </h3>
                        <p class="text-sm sm:text-base text-slate-500 leading-relaxed">
                            From pre-dawn inversion alerts to sea-breeze clearance forecasts, Alveo gives you clear Go or Postpone decisions without second-guessing.
                        </p>
                    </div>
                </div>

                <!-- Right Visual Card -->
                <div class="rounded-3xl bg-slate-50/70 border border-slate-200/80 overflow-hidden flex flex-col">
                    <div class="aspect-16/8 w-full overflow-hidden bg-slate-100 border-b border-slate-200/60">
                        <img src="{{ asset('images/landing/card-sync.svg') }}"
                             alt="Seamless Calendar and Dispatch Sync"
                             class="w-full h-full object-cover" />
                    </div>
                    <div class="p-7 sm:p-8 space-y-2.5">
                        <h3 class="text-xl font-bold text-slate-900">
                            Seamless Tool Sync
                        </h3>
                        <p class="text-sm sm:text-base text-slate-500 leading-relaxed">
                            No paid webhooks or complex OAuth setup required. Export your optimal window directly to the calendar and messaging tools you already use.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 5: "Connects with Your Tools" Floating Squared Icons & Custom Background Card -->
        <section class="py-16 sm:py-24 px-6">
            <div class="max-w-6xl mx-auto">
                <div class="relative flex flex-col lg:flex-row items-center justify-center gap-8 lg:gap-10">

                    <!-- Left Floating Squared App Icons (Google Calendar, Microsoft 365, Outlook.com, Yahoo Calendar) -->
                    <div class="grid grid-cols-4 lg:grid-cols-2 gap-4 sm:gap-5 shrink-0">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white border border-slate-200/90 shadow-xs p-2.5 flex items-center justify-center lg:-translate-y-3"
                             title="Google Calendar Deep Link">
                            <img src="{{ asset('images/landing/icon-google-calendar.svg') }}"
                                 alt="Google Calendar"
                                 class="w-full h-full object-contain" />
                        </div>

                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white border border-slate-200/90 shadow-xs p-2.5 flex items-center justify-center lg:translate-y-4"
                             title="Microsoft 365 Calendar Deep Link">
                            <img src="{{ asset('images/landing/icon-microsoft-365.svg') }}"
                                 alt="Microsoft 365 Calendar"
                                 class="w-full h-full object-contain" />
                        </div>

                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white border border-slate-200/90 shadow-xs p-2.5 flex items-center justify-center lg:-translate-y-1"
                             title="Outlook.com Personal Calendar Deep Link">
                            <img src="{{ asset('images/landing/icon-outlook.svg') }}"
                                 alt="Outlook.com Calendar"
                                 class="w-full h-full object-contain" />
                        </div>

                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white border border-slate-200/90 shadow-xs p-2.5 flex items-center justify-center lg:translate-y-5"
                             title="Yahoo Calendar Deep Link">
                            <img src="{{ asset('images/landing/icon-yahoo-calendar.svg') }}"
                                 alt="Yahoo Calendar"
                                 class="w-full h-full object-contain" />
                        </div>
                    </div>

                    <!-- Center Card with Replaceable Background Image -->
                    <div class="relative w-full max-w-xl rounded-3xl overflow-hidden border border-slate-200/90 shadow-sm bg-white px-8 py-12 sm:px-12 sm:py-16 text-center">
                        <img src="{{ asset('images/landing/tools-bg.svg') }}"
                             alt="Integrations Card Background"
                             class="absolute inset-0 w-full h-full object-cover pointer-events-none" />

                        <div class="relative z-10 space-y-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-forest-800">
                                Zero-Overhead Integrations
                            </p>

                            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-slate-900">
                                Connects with Your Tools
                            </h2>

                            <p class="text-sm sm:text-base text-slate-600 max-w-md mx-auto leading-relaxed">
                                Push clean-air training sessions to Google Calendar, Microsoft 365, Outlook.com, Yahoo Calendar, `.ics`, Telegram, and Email completely free.
                            </p>

                            <div class="pt-3">
                                @auth
                                    <a href="{{ route('finder') }}"
                                       class="inline-flex items-center justify-center px-6 py-3 rounded-xl text-sm font-semibold bg-forest-900 hover:bg-forest-800 text-white shadow-xs transition">
                                        Open Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('register') }}"
                                       class="inline-flex items-center justify-center px-6 py-3 rounded-xl text-sm font-semibold bg-forest-900 hover:bg-forest-800 text-white shadow-xs transition">
                                        Use Alveo for Free
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>

                    <!-- Right Floating Squared App Icons (Telegram, Gmail/Mailto, Apple iCal .ics, Club Dispatch) -->
                    <div class="grid grid-cols-4 lg:grid-cols-2 gap-4 sm:gap-5 shrink-0">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white border border-slate-200/90 shadow-xs p-2.5 flex items-center justify-center lg:translate-y-3"
                             title="Telegram Pre-Populated Share">
                            <img src="{{ asset('images/landing/icon-telegram.svg') }}"
                                 alt="Telegram Share"
                                 class="w-full h-full object-contain" />
                        </div>

                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white border border-slate-200/90 shadow-xs p-2.5 flex items-center justify-center lg:-translate-y-4"
                             title="Email and Gmail Pre-Populated Mailto">
                            <img src="{{ asset('images/landing/icon-gmail.svg') }}"
                                 alt="Email Mailto Share"
                                 class="w-full h-full object-contain" />
                        </div>

                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white border border-slate-200/90 shadow-xs p-2.5 flex items-center justify-center lg:translate-y-4"
                             title="Apple Calendar and Universal RFC 5545 .ics">
                            <img src="{{ asset('images/landing/icon-ical.svg') }}"
                                 alt="Universal iCal .ics"
                                 class="w-full h-full object-contain" />
                        </div>

                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white border border-slate-200/90 shadow-xs p-2.5 flex items-center justify-center lg:-translate-y-2"
                             title="Alveo Club Broadcast Dispatch">
                            <img src="{{ asset('images/landing/icon-dispatch.svg') }}"
                                 alt="Club Broadcast Dispatch"
                                 class="w-full h-full object-contain" />
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <!-- Minimal Footer -->
    <footer class="border-t border-slate-100 py-10 px-6">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400 text-center sm:text-left">
            <span class="font-bold text-sm text-slate-900">Alveo</span>
            <span class="text-center sm:text-right">Aerobic Clean-Air Window & Pulmonary Exposure Engine</span>
        </div>
    </footer>

</body>
</html>

