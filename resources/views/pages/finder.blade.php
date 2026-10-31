<x-app-layout title="Dashboard">
@php
    $cur = $evaluation['current_conditions'];
    $rec = $evaluation['recommendation'];
    $loc = $evaluation['location'];
    $act = $evaluation['activity_type'];
    $dur = $evaluation['duration_minutes'];
    $isDelay = $evaluation['status'] === 'DELAY';
    $windVal = $unitSystem === 'imperial' ? $cur['wind_speed_mph'] : $cur['wind_speed_kmh'];
    $windUnit = $unitSystem === 'imperial' ? 'mph' : 'km/h';
@endphp

<div x-data="{
        activeDay: 0,
        copiedTelegram: false,
        locating: false,
        copyTelegramCard(text) {
            navigator.clipboard.writeText(text);
            this.copiedTelegram = true;
            setTimeout(() => { this.copiedTelegram = false; }, 2500);
        },
        useBrowserGps() {
            if (!navigator.geolocation) return;
            this.locating = true;
            navigator.geolocation.getCurrentPosition((pos) => {
                const url = new URL(window.location.href);
                url.searchParams.set('lat', pos.coords.latitude.toFixed(4));
                url.searchParams.set('lon', pos.coords.longitude.toFixed(4));
                url.searchParams.set('location', 'Current GPS Position');
                url.searchParams.delete('route_id');
                window.location.href = url.toString();
            }, () => {
                this.locating = false;
            });
        }
    }"
    class="space-y-6">

    <!-- Top 4 KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- Card 1: Total Exposure Delta -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-2xs">
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-200">
                    <x-icon name="bar-chart-2" class="w-4 h-4" />
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-forest-50 dark:bg-forest-950 text-forest-800 dark:text-forest-300 tabular-nums">
                    <x-icon name="arrow-up-right" class="w-3 h-3" />
                    {{ round($rec['improvement_pct']) }}% cleaner
                </span>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Exposure Delta</p>
            <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white tabular-nums">
                -{{ round($rec['improvement_pct']) }}% PM2.5
            </p>
        </div>

        <!-- Card 2: Optimal Window -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-2xs">
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-200">
                    <x-icon name="clock" class="w-4 h-4" />
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-semibold {{ $isDelay ? 'bg-amber-50 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300' : 'bg-forest-50 dark:bg-forest-950 text-forest-800 dark:text-forest-300' }} tabular-nums">
                    {{ $rec['projected_pm2_5'] }} µg/m³
                </span>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Optimal Window</p>
            <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white tabular-nums">
                {{ $rec['short_window'] }}
            </p>
        </div>

        <!-- Card 3: Resting / Exertion AQI -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-2xs">
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-200">
                    <x-icon name="gauge" class="w-4 h-4" />
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-forest-50 dark:bg-forest-950 text-forest-800 dark:text-forest-300 tabular-nums">
                    EPA {{ $cur['us_epa_index'] }}/6 • DEFRA {{ $cur['gb_defra_index'] }}/10
                </span>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Resting / Exertion AQI</p>
            <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white tabular-nums">
                {{ $cur['us_aqi'] }} <span class="text-sm font-medium text-slate-500 dark:text-slate-400">/ {{ $cur['exertion_risk_score'] }} Exertion</span>
            </p>
        </div>

        <!-- Card 4: Surface Wind Flow -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-2xs">
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-200">
                    <x-icon name="wind" class="w-4 h-4" />
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-forest-50 dark:bg-forest-950 text-forest-800 dark:text-forest-300 tabular-nums">
                    {{ $cur['temp_c'] }}°C • UV {{ $cur['uv'] }}
                </span>
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Surface Wind Flow</p>
            <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white tabular-nums">
                {{ $windVal }} <span class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $windUnit }} {{ $cur['wind_dir'] }}</span>
            </p>
        </div>
    </div>

    <!-- Search & Activity Control Bar -->
    <form method="GET" action="{{ route('finder') }}" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-2xs space-y-3.5">
        <div class="flex flex-col md:flex-row items-stretch md:items-center gap-2.5">
            <div class="relative flex-1 flex items-center">
                <x-icon name="map-pin" class="w-4 h-4 text-slate-400 absolute left-3.5 pointer-events-none" />
                <input type="text"
                       name="location"
                       value="{{ $loc['name'] }}"
                       placeholder="Enter training corridor (e.g., GBK Outer Perimeter, Sudirman, PIK)"
                       class="input input-bordered w-full pl-10 pr-24 bg-slate-50/70 dark:bg-slate-800/70 border-slate-200 dark:border-slate-700 focus:border-forest-800 text-sm rounded-xl h-11" />
                <button type="button"
                        @click="useBrowserGps()"
                        class="absolute right-2.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-200 hover:border-forest-700 transition">
                    <span x-text="locating ? 'Locating...' : 'GPS'">GPS</span>
                </button>
            </div>

            <!-- Preset Corridor Quick Select -->
            <div class="flex items-center gap-2">
                <select name="route_id"
                        onchange="this.form.submit()"
                        class="select select-bordered bg-slate-50/70 dark:bg-slate-800/70 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-11 min-h-0">
                    <option value="">Quick Corridor Presets...</option>
                    @foreach ($corridors as $corridor)
                        <option value="{{ $corridor->id }}" @selected($selectedRoute && $selectedRoute->id === $corridor->id)>
                            {{ $corridor->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                        title="Evaluate Corridor"
                        class="btn bg-forest-900 hover:bg-forest-800 text-white border-0 rounded-xl h-11 min-h-0 px-4">
                    <x-icon name="arrow-right" class="w-4 h-4" />
                </button>
            </div>
        </div>

        <!-- Activity & Duration Pills -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Activity Pills -->
                <div class="inline-flex items-center p-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200/70 dark:border-slate-700">
                    @foreach (['running' => 'Run', 'cycling' => 'Bike', 'walking' => 'Walk'] as $actKey => $actLabel)
                        <a href="{{ route('finder', ['location' => $loc['name'], 'lat' => $loc['latitude'], 'lon' => $loc['longitude'], 'activity' => $actKey, 'duration' => $dur]) }}"
                           class="px-3.5 py-1 rounded-lg text-xs font-semibold transition {{ $act === $actKey ? 'bg-forest-900 text-white shadow-2xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                            {{ $actLabel }}
                        </a>
                    @endforeach
                </div>

                <!-- Duration Pills -->
                <div class="inline-flex items-center p-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200/70 dark:border-slate-700 tabular-nums">
                    @foreach ([30, 60, 90, 120] as $durOpt)
                        <a href="{{ route('finder', ['location' => $loc['name'], 'lat' => $loc['latitude'], 'lon' => $loc['longitude'], 'activity' => $act, 'duration' => $durOpt]) }}"
                           class="px-3 py-1 rounded-lg text-xs font-semibold transition {{ $dur === $durOpt ? 'bg-forest-900 text-white shadow-2xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                            {{ $durOpt }}m
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="text-xs text-slate-500 dark:text-slate-400 tabular-nums">
                Coordinates: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ number_format($loc['latitude'], 4) }}, {{ number_format($loc['longitude'], 4) }}</span>
                • Safety Ceiling: <span class="font-semibold text-forest-800 dark:text-forest-400">≤ {{ $evaluation['pm25_threshold'] }} µg/m³</span>
            </div>
        </div>
    </form>

    <!-- Main 2-Column Operational Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Go / Postpone Verdict Hero Card (5 cols) -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 lg:p-7 shadow-2xs space-y-6">
            <div class="flex items-center justify-between gap-2">
                @if ($isDelay)
                    <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-bold tracking-wide uppercase bg-amber-50 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        Postpone Session
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-bold tracking-wide uppercase bg-forest-50 dark:bg-forest-950 text-forest-900 dark:text-forest-300 border border-forest-200 dark:border-forest-800">
                        <span class="w-2 h-2 rounded-full bg-forest-600"></span>
                        Go - Clear Window
                    </span>
                @endif

                <span class="text-xs font-semibold tracking-wider uppercase text-slate-400 dark:text-slate-500 tabular-nums">
                    {{ $rec['delta_label'] }}
                </span>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Optimal Rescheduled Window
                </p>
                <h2 class="mt-1.5 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white tabular-nums">
                    {{ $rec['window_display'] }}
                </h2>

                <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-forest-50 dark:bg-forest-950/90 text-forest-800 dark:text-forest-300 border border-forest-200/70 dark:border-forest-800 tabular-nums">
                    <x-icon name="arrow-down" class="w-3.5 h-3.5 text-forest-700 dark:text-forest-400" />
                    <span>-{{ round($rec['improvement_pct']) }}% Inhaled PM2.5 Exposure ({{ $rec['projected_dose_ug'] }} µg vs {{ $cur['inhaled_dose_ug'] }} µg)</span>
                </div>
            </div>

            <!-- Micro-Meteorological Analysis Box -->
            <div class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-2">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-800 dark:text-slate-200">
                    <x-icon name="radio" class="w-4 h-4 text-forest-700 dark:text-forest-400" />
                    <span>Micro-Meteorological Analysis</span>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    {{ $evaluation['ai_briefing'] }}
                </p>
            </div>

            <!-- Zero-Overhead Client Action Buttons -->
            <div class="space-y-2.5 pt-1">
                <a href="{{ $evaluation['actions']['google_calendar_url'] }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="btn w-full bg-forest-900 hover:bg-forest-800 text-white border-0 rounded-xl font-semibold text-sm h-11 shadow-xs inline-flex items-center justify-center gap-2">
                    <x-icon name="calendar-plus" class="w-4 h-4" />
                    <span>Add to Google Calendar</span>
                </a>

                <!-- Multi-Platform Calendar Deep Links (M365, Outlook.com, Yahoo, .ics) -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <a href="{{ $evaluation['actions']['m365_calendar_url'] }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="px-2.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-forest-700 dark:hover:border-forest-500 bg-slate-50/70 dark:bg-slate-800/70 text-xs font-semibold text-slate-700 dark:text-slate-200 text-center transition">
                        Microsoft 365
                    </a>
                    <a href="{{ $evaluation['actions']['outlook_calendar_url'] }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="px-2.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-forest-700 dark:hover:border-forest-500 bg-slate-50/70 dark:bg-slate-800/70 text-xs font-semibold text-slate-700 dark:text-slate-200 text-center transition">
                        Outlook.com
                    </a>
                    <a href="{{ $evaluation['actions']['yahoo_calendar_url'] }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="px-2.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-forest-700 dark:hover:border-forest-500 bg-slate-50/70 dark:bg-slate-800/70 text-xs font-semibold text-slate-700 dark:text-slate-200 text-center transition">
                        Yahoo Cal
                    </a>
                    <a href="{{ route('calendar.ics', ['lat' => $loc['latitude'], 'lon' => $loc['longitude'], 'activity' => $act, 'duration' => $dur, 'location' => $loc['name']]) }}"
                       class="px-2.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-forest-700 dark:hover:border-forest-500 bg-slate-50/70 dark:bg-slate-800/70 text-xs font-semibold text-slate-700 dark:text-slate-200 text-center inline-flex items-center justify-center gap-1 transition">
                        <x-icon name="download" class="w-3.5 h-3.5" />
                        <span>.ics File</span>
                    </a>
                </div>

                <!-- Pre-Populated Share Channels (Telegram, Mailto, Clipboard) -->
                <div class="pt-1 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <a href="{{ $evaluation['actions']['telegram_share_url'] }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-forest-800 dark:hover:text-forest-300 transition py-1">
                            <x-icon name="send" class="w-3.5 h-3.5 text-forest-700 dark:text-forest-400" />
                            <span>Telegram</span>
                        </a>

                        <a href="{{ $evaluation['actions']['mailto_share_url'] }}"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-forest-800 dark:hover:text-forest-300 transition py-1">
                            <x-icon name="mail" class="w-3.5 h-3.5 text-forest-700 dark:text-forest-400" />
                            <span>Email Dispatch</span>
                        </a>
                    </div>

                    <button type="button"
                            @click="copyTelegramCard(@js($evaluation['actions']['telegram_card_text']))"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-forest-800 dark:hover:text-forest-300 transition py-1">
                        <x-icon name="copy" x-show="!copiedTelegram" class="w-3.5 h-3.5" />
                        <x-icon name="check" x-show="copiedTelegram" x-cloak class="w-3.5 h-3.5 text-forest-700" />
                        <span x-text="copiedTelegram ? 'Copied Card!' : 'Copy Card'">
                            Copy Card
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Column: Window Timeline & Dispersion Bar (7 cols) -->
        <div class="lg:col-span-7 space-y-5">
            <!-- Timeline Header & 3-Day Free Tier Horizon Tabs -->
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    Window Timeline
                </h3>

                <div class="inline-flex items-center p-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200/70 dark:border-slate-700">
                    @foreach ($evaluation['timeline_days'] as $dIdx => $tDay)
                        <button type="button"
                                @click="activeDay = {{ $dIdx }}"
                                :class="activeDay === {{ $dIdx }} ? 'bg-forest-900 text-white shadow-2xs font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                                class="px-3.5 py-1 rounded-lg text-xs transition">
                            {{ $tDay['day_label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Hourly Slots List for Active Day -->
            @foreach ($evaluation['timeline_days'] as $dIdx => $tDay)
                <div x-show="activeDay === {{ $dIdx }}"
                     x-cloak
                     class="space-y-2.5">
                    @foreach ($tDay['slots'] as $slot)
                        <div class="flex items-center justify-between p-4 rounded-2xl border transition {{ $slot['is_optimal']
                            ? 'bg-forest-50/90 dark:bg-forest-950/70 border-forest-300 dark:border-forest-700 shadow-2xs'
                            : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800' }}">

                            <div class="flex items-center gap-3.5">
                                @if ($slot['is_optimal'])
                                    <div class="w-6 h-6 rounded-full bg-forest-800 text-white flex items-center justify-center shrink-0">
                                        <x-icon name="check" class="w-3.5 h-3.5" />
                                    </div>
                                @elseif ($slot['severity'] === 'high')
                                    <span class="w-3 h-3 rounded-full bg-red-500 shrink-0 ml-1.5 mr-1.5"></span>
                                @elseif ($slot['severity'] === 'commuter')
                                    <span class="w-3 h-3 rounded-full bg-slate-400 shrink-0 ml-1.5 mr-1.5"></span>
                                @elseif ($slot['severity'] === 'moderate')
                                    <span class="w-3 h-3 rounded-full bg-amber-500 shrink-0 ml-1.5 mr-1.5"></span>
                                @else
                                    <span class="w-3 h-3 rounded-full bg-emerald-500 shrink-0 ml-1.5 mr-1.5"></span>
                                @endif

                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-slate-900 dark:text-white tabular-nums">
                                            {{ $slot['time_range'] }}
                                        </span>
                                        @if ($slot['is_optimal'])
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-forest-800 text-white">
                                                Optimal
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ $slot['regime_tag'] }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-right tabular-nums">
                                <span class="text-sm font-bold {{ $slot['is_optimal'] ? 'text-forest-900 dark:text-forest-300' : 'text-slate-900 dark:text-white' }}">
                                    {{ round($slot['pm2_5']) }} µg/m³
                                </span>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                                    {{ $slot['wind_kph'] }} km/h {{ $slot['wind_dir'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach

            <!-- Bottom Dispersion Probability & Air Clearance Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-2xs space-y-3.5">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                        Dispersion Probability &amp; Air Clearance
                    </h4>
                    <span class="text-xs font-medium text-forest-800 dark:text-forest-300 tabular-nums">
                        Mixing Depth Peak: 09:00 - 11:00
                    </span>
                </div>

                <!-- Segmented Progression Bar -->
                <div class="h-2.5 w-full rounded-full overflow-hidden flex bg-slate-100 dark:bg-slate-800">
                    <div class="w-1/4 bg-red-500"></div>
                    <div class="w-1/4 bg-amber-500"></div>
                    <div class="w-1/4 bg-forest-700"></div>
                    <div class="w-1/4 bg-emerald-500"></div>
                </div>

                <div class="grid grid-cols-4 gap-2 text-[11px] leading-tight text-slate-500 dark:text-slate-400 tabular-nums">
                    <div>
                        <span class="font-semibold text-slate-700 dark:text-slate-300 block">06:00</span>
                        <span>(Stagnant)</span>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-700 dark:text-slate-300 block">08:00</span>
                        <span>(Breeze Start)</span>
                    </div>
                    <div class="text-forest-800 dark:text-forest-300 font-semibold">
                        <span class="block">09:00 (Clean</span>
                        <span>Window)</span>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-700 dark:text-slate-300 block">11:00 (Thermal</span>
                        <span>Mix)</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</x-app-layout>

