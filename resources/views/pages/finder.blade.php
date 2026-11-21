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
        selectedDayIdx: 0,
        selectedSlot: @js(collect($evaluation['timeline_days'][0]['slots'] ?? [])->firstWhere('is_optimal', true) ?? ($evaluation['timeline_days'][0]['slots'][0] ?? null)),
        copiedTelegram: false,
        locating: false,
        locationQuery: @js($loc['name']),
        selectedLat: @js((string) $loc['latitude']),
        selectedLon: @js((string) $loc['longitude']),
        suggestions: [],
        dropdownOpen: false,
        activeIndex: -1,
        searchingLocations: false,
        searchTimer: null,
        selectTimelineSlot(dayIdx, slot) {
            this.selectedDayIdx = dayIdx;
            this.selectedSlot = slot;
        },
        formatCompactUtc(iso) {
            const d = new Date(iso);
            const pad = (n) => String(n).padStart(2, '0');
            return `${d.getUTCFullYear()}${pad(d.getUTCMonth() + 1)}${pad(d.getUTCDate())}T${pad(d.getUTCHours())}${pad(d.getUTCMinutes())}${pad(d.getUTCSeconds())}Z`;
        },
        formatIsoUtc(iso) {
            return new Date(iso).toISOString().replace(/\.\d{3}Z$/, 'Z');
        },
        calendarDetails() {
            const slot = this.selectedSlot;
            if (!slot) return @js($evaluation['ai_briefing']);
            const label = slot.is_optimal ? 'Optimal Aerobic Window' : 'Selected Training Window';
            return `${label} verified by Alveo.\nProjected PM2.5: ${Number(slot.pm2_5).toFixed(1)} ug/m3 (-${Math.round(slot.improvement_pct)}% inhaled exposure)\n\nMicro-Meteorological Analysis:\n` + @js($evaluation['ai_briefing']);
        },
        calendarTitle() {
            return @js(sprintf('Alveo Clean-Air %s Session (%s)', ucfirst($act), $loc['name']));
        },
        get googleCalendarUrl() {
            if (!this.selectedSlot) return @js($evaluation['actions']['google_calendar_url']);
            const params = new URLSearchParams({
                action: 'TEMPLATE',
                text: this.calendarTitle(),
                dates: `${this.formatCompactUtc(this.selectedSlot.start_iso)}/${this.formatCompactUtc(this.selectedSlot.end_iso)}`,
                details: this.calendarDetails(),
                location: @js($loc['name']),
            });
            return `https://calendar.google.com/calendar/render?${params.toString()}`;
        },
        outlookUrl(host) {
            if (!this.selectedSlot) return host.includes('office') ? @js($evaluation['actions']['m365_calendar_url']) : @js($evaluation['actions']['outlook_calendar_url']);
            const params = new URLSearchParams({
                path: '/calendar/action/compose',
                rru: 'addevent',
                subject: this.calendarTitle(),
                startdt: this.formatIsoUtc(this.selectedSlot.start_iso),
                enddt: this.formatIsoUtc(this.selectedSlot.end_iso),
                body: this.calendarDetails(),
                location: @js($loc['name']),
            });
            return `https://${host}/calendar/0/deeplink/compose?${params.toString()}`;
        },
        get m365CalendarUrl() {
            return this.outlookUrl('outlook.office.com');
        },
        get outlookCalendarUrl() {
            return this.outlookUrl('outlook.live.com');
        },
        get yahooCalendarUrl() {
            if (!this.selectedSlot) return @js($evaluation['actions']['yahoo_calendar_url']);
            const params = new URLSearchParams({
                v: '60',
                title: this.calendarTitle(),
                st: this.formatCompactUtc(this.selectedSlot.start_iso),
                et: this.formatCompactUtc(this.selectedSlot.end_iso),
                desc: this.calendarDetails(),
                in_loc: @js($loc['name']),
            });
            return `https://calendar.yahoo.com/?${params.toString()}`;
        },
        get icsDownloadUrl() {
            const base = @js(route('calendar.ics', ['lat' => $loc['latitude'], 'lon' => $loc['longitude'], 'activity' => $act, 'duration' => $dur, 'location' => $loc['name']]));
            if (!this.selectedSlot) return base;
            const url = new URL(base, window.location.origin);
            url.searchParams.set('start_iso', this.selectedSlot.start_iso);
            url.searchParams.set('end_iso', this.selectedSlot.end_iso);
            url.searchParams.set('pm2_5', String(this.selectedSlot.pm2_5));
            return url.toString();
        },
        copyTelegramCard(text) {
            navigator.clipboard.writeText(text);
            this.copiedTelegram = true;
            setTimeout(() => { this.copiedTelegram = false; }, 2500);
        },
        onLocationInput() {
            this.selectedLat = '';
            this.selectedLon = '';
            this.activeIndex = -1;
            clearTimeout(this.searchTimer);
            const q = this.locationQuery.trim();
            if (q.length < 2) {
                this.suggestions = [];
                this.dropdownOpen = false;
                return;
            }
            this.searchTimer = setTimeout(() => this.fetchLocations(q), 180);
        },
        async fetchLocations(q) {
            this.searchingLocations = true;
            try {
                const res = await fetch(`{{ route('api.locations.search') }}?q=${encodeURIComponent(q)}&limit=4`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const payload = await res.json();
                this.suggestions = Array.isArray(payload.data) ? payload.data : [];
                this.dropdownOpen = this.suggestions.length > 0;
            } finally {
                this.searchingLocations = false;
            }
        },
        selectSuggestion(item) {
            this.locationQuery = item.label;
            this.selectedLat = String(item.lat);
            this.selectedLon = String(item.lon);
            this.dropdownOpen = false;
            this.activeIndex = -1;
            const url = new URL(window.location.href);
            url.searchParams.set('location', item.label);
            url.searchParams.set('lat', Number(item.lat).toFixed(4));
            url.searchParams.set('lon', Number(item.lon).toFixed(4));
            url.searchParams.set('activity', @js($act));
            url.searchParams.set('duration', @js((string) $dur));
            url.searchParams.delete('route_id');
            window.location.href = url.toString();
        },
        moveSelection(step) {
            if (!this.dropdownOpen || this.suggestions.length === 0) return;
            const max = this.suggestions.length - 1;
            if (this.activeIndex < 0) {
                this.activeIndex = step > 0 ? 0 : max;
            } else {
                this.activeIndex = (this.activeIndex + step + this.suggestions.length) % this.suggestions.length;
            }
        },
        handleLocationKeydown(event) {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                this.moveSelection(1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                this.moveSelection(-1);
            } else if (event.key === 'Escape') {
                this.dropdownOpen = false;
                this.activeIndex = -1;
            } else if (event.key === 'Enter' && this.dropdownOpen && this.activeIndex >= 0 && this.suggestions[this.activeIndex]) {
                event.preventDefault();
                this.selectSuggestion(this.suggestions[this.activeIndex]);
            }
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
    <form method="GET" action="{{ route('finder') }}" @click.outside="dropdownOpen = false" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-2xs space-y-3.5">
        <input type="hidden" name="activity" value="{{ $act }}" />
        <input type="hidden" name="duration" value="{{ $dur }}" />
        <input type="hidden" name="lat" :value="selectedLat" :disabled="!selectedLat" />
        <input type="hidden" name="lon" :value="selectedLon" :disabled="!selectedLon" />

        <div class="flex flex-col md:flex-row items-stretch md:items-center gap-2.5">
            <div class="relative flex-1 flex items-center">
                <x-icon name="map-pin" class="w-4 h-4 text-slate-400 absolute left-3.5 pointer-events-none" />
                <input type="text"
                       name="location"
                       x-model="locationQuery"
                       @input="onLocationInput()"
                       @focus="if (suggestions.length > 0) dropdownOpen = true"
                       @keydown="handleLocationKeydown($event)"
                       autocomplete="off"
                       placeholder="Search Indonesia province, city, or district (e.g., Banten, Bandung, Tanah Abang)"
                       class="input input-bordered w-full pl-10 pr-24 bg-slate-50/70 dark:bg-slate-800/70 border-slate-200 dark:border-slate-700 focus:border-forest-800 text-sm rounded-xl h-11" />
                <button type="button"
                        @click="useBrowserGps()"
                        class="absolute right-2.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-200 hover:border-forest-700 transition">
                    <span x-text="locating ? 'Locating...' : 'GPS'">GPS</span>
                </button>
            </div>

            <!-- Preset Corridor Quick Select -->
            <div class="flex items-center gap-2 w-full md:w-auto">
                <select name="route_id"
                        onchange="this.form.requestSubmit()"
                        class="select select-bordered flex-1 min-w-0 max-w-full bg-slate-50/70 dark:bg-slate-800/70 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-11 min-h-0">
                    <option value="">Quick Corridor Presets...</option>
                    @foreach ($corridors as $corridor)
                        <option value="{{ $corridor->id }}" @selected($selectedRoute && $selectedRoute->id === $corridor->id)>
                            {{ $corridor->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                        title="Evaluate Corridor"
                        class="btn shrink-0 bg-forest-900 hover:bg-forest-800 text-white border-0 rounded-xl h-11 min-h-0 px-4">
                    <x-icon name="arrow-right" class="w-4 h-4" />
                </button>
            </div>
        </div>

        <!-- Inline Location Suggestions (Max 4, inside Search & Activity Control Bar) -->
        <div x-show="dropdownOpen && suggestions.length > 0"
             x-cloak
             class="rounded-xl border border-slate-200/90 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 divide-y divide-slate-200/70 dark:divide-slate-700/70 overflow-hidden">
            <template x-for="(item, idx) in suggestions" :key="item.id">
                <button type="button"
                        @click="selectSuggestion(item)"
                        @mouseenter="activeIndex = idx"
                        :class="activeIndex === idx ? 'bg-forest-50 dark:bg-forest-950/70' : 'hover:bg-white dark:hover:bg-slate-800'"
                        class="w-full px-3.5 py-2.5 text-left flex items-center justify-between gap-3 transition">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-900 dark:text-white truncate" x-text="item.label"></p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 tabular-nums" x-text="`${Number(item.lat).toFixed(4)}, ${Number(item.lon).toFixed(4)}`"></p>
                    </div>
                    <span class="shrink-0 px-2 py-0.5 rounded-md text-[10px] font-semibold uppercase tracking-wider bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700 text-slate-600 dark:text-slate-300"
                          x-text="item.type"></span>
                </button>
            </template>
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

        <!-- Left Column: Recommended Window Hero Card (5 cols) -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 lg:p-7 shadow-2xs space-y-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"
                   x-text="selectedSlot && !selectedSlot.is_optimal ? `Selected Training Window (${selectedSlot.day_label})` : 'Optimal Rescheduled Window'">
                    Optimal Rescheduled Window
                </p>
                <h2 class="mt-1.5 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white tabular-nums"
                    x-text="selectedSlot ? selectedSlot.window_display : @js($rec['window_display'])">
                    {{ $rec['window_display'] }}
                </h2>

                <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-forest-50 dark:bg-forest-950/90 text-forest-800 dark:text-forest-300 border border-forest-200/70 dark:border-forest-800 tabular-nums">
                    <x-icon name="arrow-down" class="w-3.5 h-3.5 text-forest-700 dark:text-forest-400" />
                    <span x-text="selectedSlot ? `-${Math.round(selectedSlot.improvement_pct)}% Inhaled PM2.5 Exposure (${selectedSlot.projected_dose_ug} µg vs {{ $cur['inhaled_dose_ug'] }} µg)` : '-{{ round($rec['improvement_pct']) }}% Inhaled PM2.5 Exposure ({{ $rec['projected_dose_ug'] }} µg vs {{ $cur['inhaled_dose_ug'] }} µg)'">
                        -{{ round($rec['improvement_pct']) }}% Inhaled PM2.5 Exposure ({{ $rec['projected_dose_ug'] }} µg vs {{ $cur['inhaled_dose_ug'] }} µg)
                    </span>
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
            <div class="pt-1">
                <a :href="googleCalendarUrl"
                   href="{{ $evaluation['actions']['google_calendar_url'] }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="btn w-full bg-forest-900 hover:bg-forest-800 text-white border-0 rounded-xl font-semibold text-sm h-11 shadow-xs inline-flex items-center justify-center gap-2 mb-2.5">
                    <x-icon name="calendar-plus" class="w-4 h-4" />
                    <span>Add to Google Calendar</span>
                </a>

                <!-- Multi-Platform Calendar Deep Links (M365, Outlook, Yahoo, .ics) -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3.5 sm:mb-4">
                    <a :href="m365CalendarUrl"
                       href="{{ $evaluation['actions']['m365_calendar_url'] }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="h-11 px-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-forest-700 dark:hover:border-forest-500 bg-slate-50/70 dark:bg-slate-800/70 text-xs font-semibold text-slate-700 dark:text-slate-200 flex items-center justify-center text-center leading-tight transition">
                        <span class="lg:hidden">Microsoft 365</span>
                        <span class="hidden lg:inline">MS365</span>
                    </a>
                    <a :href="outlookCalendarUrl"
                       href="{{ $evaluation['actions']['outlook_calendar_url'] }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="h-11 px-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-forest-700 dark:hover:border-forest-500 bg-slate-50/70 dark:bg-slate-800/70 text-xs font-semibold text-slate-700 dark:text-slate-200 flex items-center justify-center text-center transition">
                        Outlook
                    </a>
                    <a :href="yahooCalendarUrl"
                       href="{{ $evaluation['actions']['yahoo_calendar_url'] }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="h-11 px-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-forest-700 dark:hover:border-forest-500 bg-slate-50/70 dark:bg-slate-800/70 text-xs font-semibold text-slate-700 dark:text-slate-200 flex items-center justify-center text-center transition">
                        <span class="lg:hidden">Yahoo Cal</span>
                        <span class="hidden lg:inline">Yahoo</span>
                    </a>
                    <a :href="icsDownloadUrl"
                       href="{{ route('calendar.ics', ['lat' => $loc['latitude'], 'lon' => $loc['longitude'], 'activity' => $act, 'duration' => $dur, 'location' => $loc['name']]) }}"
                       class="h-11 px-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-forest-700 dark:hover:border-forest-500 bg-slate-50/70 dark:bg-slate-800/70 text-xs font-semibold text-slate-700 dark:text-slate-200 flex items-center justify-center text-center gap-1.5 transition">
                        <x-icon name="download" class="w-3.5 h-3.5 shrink-0" />
                        <span class="lg:hidden">.ics File</span>
                        <span class="hidden lg:inline">.ics</span>
                    </a>
                </div>

                <!-- Pre-Populated Share Channels (Telegram, Mailto, Clipboard) -->
                <div class="mt-3.5 sm:mt-4 pt-3.5 sm:pt-4 flex flex-wrap items-center justify-between gap-2.5 border-t border-slate-100 dark:border-slate-800">
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
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2.5">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    Window Timeline
                </h3>

                <div class="inline-flex items-center p-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200/70 dark:border-slate-700 w-full sm:w-auto">
                    @foreach ($evaluation['timeline_days'] as $dIdx => $tDay)
                        <button type="button"
                                @click="activeDay = {{ $dIdx }}"
                                :class="activeDay === {{ $dIdx }} ? 'bg-forest-900 text-white shadow-2xs font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                                class="flex-1 sm:flex-initial text-center px-3.5 py-1 rounded-lg text-xs transition whitespace-nowrap">
                            {{ $tDay['day_label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Hourly Slots List for Active Day (06:00-18:00, scrollable 6-row viewport) -->
            @foreach ($evaluation['timeline_days'] as $dIdx => $tDay)
                <div x-show="activeDay === {{ $dIdx }}"
                     x-cloak
                     class="max-h-[460px] overflow-y-auto pr-2 space-y-2.5">
                    @foreach ($tDay['slots'] as $slot)
                        <button type="button"
                                @click="selectTimelineSlot({{ $dIdx }}, @js($slot))"
                                :class="selectedSlot && selectedSlot.start_iso === @js($slot['start_iso'])
                                    ? 'border-forest-700 dark:border-forest-500 ring-1 ring-inset ring-forest-700/60 dark:ring-forest-500/60'
                                    : ''"
                                class="w-full text-left flex items-center justify-between p-4 rounded-2xl border transition cursor-pointer {{ $slot['is_optimal']
                                    ? 'bg-forest-50/90 dark:bg-forest-950/70 border-forest-300 dark:border-forest-700 shadow-2xs'
                                    : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700' }}">

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
                        </button>
                    @endforeach
                </div>
            @endforeach

            <!-- Bottom Dispersion Probability & Air Clearance Card -->
            @foreach ($evaluation['timeline_days'] as $dIdx => $tDay)
                <div x-show="activeDay === {{ $dIdx }}"
                     x-cloak
                     class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-2xs space-y-3.5">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                            Dispersion Probability & Air Clearance
                        </h4>
                        <span class="text-xs font-medium text-forest-800 dark:text-forest-300 tabular-nums">
                            Optimal: {{ $tDay['short_window'] }}
                        </span>
                    </div>

                    <!-- Segmented Progression Bar (06:00-18:00) -->
                    <div class="h-2.5 w-full rounded-full overflow-hidden flex gap-px bg-slate-100 dark:bg-slate-800">
                        @foreach ($tDay['slots'] as $slot)
                            @php
                                $segColor = match ($slot['severity']) {
                                    'optimal' => 'bg-forest-700',
                                    'low' => 'bg-emerald-500',
                                    'moderate' => 'bg-amber-500',
                                    'commuter' => 'bg-slate-400',
                                    default => 'bg-red-500',
                                };
                            @endphp
                            <div class="flex-1 {{ $segColor }}" title="{{ $slot['time_range'] }}: {{ round($slot['pm2_5']) }} µg/m³"></div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-4 gap-2 text-[11px] leading-tight text-slate-500 dark:text-slate-400 tabular-nums">
                        <div>
                            <span class="font-semibold text-slate-700 dark:text-slate-300 block">06:00</span>
                            <span>Morning</span>
                        </div>
                        <div>
                            <span class="font-semibold text-slate-700 dark:text-slate-300 block">10:00</span>
                            <span>Mid-Morning</span>
                        </div>
                        <div>
                            <span class="font-semibold text-slate-700 dark:text-slate-300 block">14:00</span>
                            <span>Afternoon</span>
                        </div>
                        <div class="text-right">
                            <span class="font-semibold text-slate-700 dark:text-slate-300 block">18:00</span>
                            <span>Evening</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>
</x-app-layout>

