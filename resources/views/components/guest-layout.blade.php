@props(['title' => 'Authentication'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="alveo">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - Alveo</title>

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
        .tabular-nums { font-variant-numeric: tabular-nums; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased min-h-screen flex flex-col justify-center items-center p-6">

    <div class="w-full max-w-md">
        <!-- Simple Typography Brand Header (No Icon) -->
        <div class="text-center mb-8">
            <a href="{{ url('/') }}" class="text-3xl font-bold tracking-tight text-slate-900">
                Alveo
            </a>
            <p class="mt-1.5 text-sm text-slate-500">
                Aerobic Clean-Air Window & Exposure Engine
            </p>
        </div>

        <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs p-8">
            {{ $slot }}
        </div>
    </div>

</body>
</html>

