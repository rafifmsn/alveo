<x-app-layout title="Inhaled Dosimetry Calculator">
<div x-data="{
        pm25: {{ $activeResult['ambient_pm2_5'] }},
        o3: {{ $activeResult['ambient_o3'] }},
        pm10: {{ $activeResult['ambient_pm10'] }},
        duration: {{ $activeResult['duration_minutes'] }},
        zone: {{ $activeResult['zone'] }},
        zones: @js($zonesConfig),
        get activeZone() {
            return this.zones[this.zone] || this.zones[3];
        },
        get totalLiters() {
            return Math.round(this.activeZone.ventilation_lpm * this.duration);
        },
        get airVolumeM3() {
            return (this.totalLiters / 1000).toFixed(2);
        },
        get retainedPm25Ug() {
            return ((this.totalLiters / 1000) * this.pm25 * this.activeZone.deposition_fraction).toFixed(1);
        },
        get restingPm25Ug() {
            const r = this.zones[0];
            return (((r.ventilation_lpm * this.duration) / 1000) * this.pm25 * r.deposition_fraction).toFixed(1);
        },
        get cleanWindowPm25Ug() {
            return ((this.totalLiters / 1000) * Math.max(12, (this.pm25 * 0.38)) * this.activeZone.deposition_fraction).toFixed(1);
        },
        get multiplier() {
            const rest = parseFloat(this.restingPm25Ug);
            return rest > 0 ? (parseFloat(this.retainedPm25Ug) / rest).toFixed(1) : '1.0';
        },
        doseForZone(zNum) {
            const z = this.zones[zNum];
            return (((z.ventilation_lpm * this.duration) / 1000) * this.pm25 * z.deposition_fraction).toFixed(1);
        }
    }"
    class="space-y-6">

    <!-- Top Scientific Context Banner -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-2xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="min-w-0">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                Aerobic Minute-Ventilation & Alveolar Deposition Engine
            </h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Formula: <span class="font-semibold text-slate-700 dark:text-slate-200">Retained Dose (µg) = Ventilation Rate (m³/h) × Duration (h) × Ambient PM2.5 (µg/m³) × Deposition Fraction</span>
            </p>
        </div>

        <!-- Load Cached Corridor Snapshot -->
        <form method="GET" action="{{ route('dosimetry.index') }}" class="flex items-center gap-2 w-full lg:w-auto shrink-0">
            <select name="route_id"
                    onchange="this.form.submit()"
                    class="select select-bordered w-full lg:w-72 min-w-0 max-w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-xs rounded-xl h-10 min-h-0">
                @foreach ($corridors as $c)
                    <option value="{{ $c->id }}" @selected(($selectedRouteId ?? null) === $c->id)>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Interactive Parameter Controls (5 cols) -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-2xs space-y-6">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                 Physiological & Atmospheric Inputs
            </h3>

            <!-- Ambient PM2.5 Slider -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs font-semibold">
                    <span class="uppercase tracking-wider text-slate-500">Ambient PM2.5 Concentration</span>
                    <span class="text-sm font-bold text-forest-800 dark:text-forest-300 tabular-nums" x-text="pm25 + ' µg/m³'"></span>
                </div>
                <input type="range" min="5" max="180" step="0.5" x-model.number="pm25"
                       class="range range-xs range-primary w-full" />
                <div class="flex justify-between text-[11px] text-slate-400 tabular-nums">
                    <span>5 µg/m³ (Clean)</span>
                    <span>35 µg/m³ (Ceiling)</span>
                    <span>180 µg/m³ (Acute)</span>
                </div>
            </div>

            <!-- Workout Duration Slider -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs font-semibold">
                    <span class="uppercase tracking-wider text-slate-500">Session Duration</span>
                    <span class="text-sm font-bold text-slate-900 dark:text-white tabular-nums" x-text="duration + ' minutes'"></span>
                </div>
                <input type="range" min="15" max="180" step="15" x-model.number="duration"
                       class="range range-xs range-primary w-full" />
                <div class="flex justify-between text-[11px] text-slate-400 tabular-nums">
                    <span>15m</span>
                    <span>60m</span>
                    <span>120m</span>
                    <span>180m</span>
                </div>
            </div>

            <!-- Ambient Ozone Slider -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs font-semibold">
                    <span class="uppercase tracking-wider text-slate-500">Ground-Level Ozone (O3)</span>
                    <span class="text-sm font-bold text-slate-900 dark:text-white tabular-nums" x-text="o3 + ' µg/m³'"></span>
                </div>
                <input type="range" min="10" max="160" step="1" x-model.number="o3"
                       class="range range-xs range-primary w-full" />
            </div>

            <!-- Exertion / Heart Rate Zone Selector -->
            <div class="space-y-2.5">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Aerobic Exertion Zone
                </label>

                <div class="space-y-2">
                    @foreach ($zonesConfig as $zNum => $zData)
                        <button type="button"
                                @click="zone = {{ $zNum }}"
                                :class="zone === {{ $zNum }}
                                    ? 'bg-forest-900 text-white border-forest-900 shadow-2xs'
                                    : 'bg-slate-50 dark:bg-slate-800/70 text-slate-700 dark:text-slate-200 border-slate-200/80 dark:border-slate-700 hover:border-forest-700'"
                                class="w-full text-left p-3.5 rounded-xl border transition flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold">{{ $zData['name'] }}</p>
                                <p class="text-[11px] opacity-80 mt-0.5">{{ $zData['description'] }}</p>
                            </div>
                            <div class="text-right tabular-nums shrink-0 ml-3">
                                <span class="text-xs font-bold block">{{ $zData['ventilation_lpm'] }} L/m</span>
                                <span class="text-[10px] opacity-75">DF {{ $zData['deposition_fraction'] }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Column: Live Calculated Dosimetry Outputs (7 cols) -->
        <div class="lg:col-span-7 space-y-5">

            <!-- 3 Primary Output Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 tabular-nums">
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-2xs">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Retained Lung PM2.5</p>
                    <p class="mt-1.5 text-3xl font-extrabold text-slate-900 dark:text-white">
                        <span x-text="retainedPm25Ug"></span> <span class="text-sm font-semibold text-slate-500">µg</span>
                    </p>
                    <p class="mt-1 text-xs text-forest-800 dark:text-forest-300 font-semibold">
                        Clean Window: <span x-text="cleanWindowPm25Ug"></span> µg
                    </p>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-2xs">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Air Ventilated</p>
                    <p class="mt-1.5 text-3xl font-extrabold text-slate-900 dark:text-white">
                        <span x-text="totalLiters.toLocaleString()"></span> <span class="text-sm font-semibold text-slate-500">L</span>
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        <span x-text="airVolumeM3"></span> m³ total volume
                    </p>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-2xs">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Exertion Multiplier</p>
                    <p class="mt-1.5 text-3xl font-extrabold text-forest-800 dark:text-forest-300">
                        <span x-text="multiplier"></span>x
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        vs Resting (<span x-text="restingPm25Ug"></span> µg)
                    </p>
                </div>
            </div>

            <!-- Comparative Bar Matrix Across All Zones -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">
                            Pulmonary Deposition by Exertion Zone
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Why moderate outdoor AQI becomes hazardous when nasal filtration switches to high-velocity mouth breathing.
                        </p>
                    </div>
                </div>

                <div class="space-y-3.5 pt-2 tabular-nums">
                    @foreach ($zonesConfig as $zNum => $zData)
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-700 dark:text-slate-200">
                                    {{ $zData['name'] }} ({{ $zData['ventilation_lpm'] }} L/min)
                                </span>
                                <span class="font-bold text-slate-900 dark:text-white" x-text="doseForZone({{ $zNum }}) + ' µg retained'"></span>
                            </div>
                            <div class="h-2.5 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-300 {{ $zNum === 0 ? 'bg-slate-400' : ($zNum <= 2 ? 'bg-forest-600' : ($zNum === 3 ? 'bg-forest-800' : 'bg-amber-600')) }}"
                                     :style="'width: ' + Math.min(100, Math.max(4, (doseForZone({{ $zNum }}) / Math.max(1, doseForZone(5))) * 100)) + '%'">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Physiological Takeaway & Recovery Card -->
            <div class="bg-forest-50/70 dark:bg-forest-950/60 border border-forest-200 dark:border-forest-800/80 rounded-2xl p-5 space-y-2">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-forest-900 dark:text-forest-300">
                    <x-icon name="heart-pulse" class="w-4 h-4" />
                    <span>Sports Pulmonology & Recovery Guidance</span>
                </div>
                <p class="text-sm text-slate-700 dark:text-slate-200 leading-relaxed">
                    {{ $activeResult['recovery_advisory'] }}
                </p>
            </div>

        </div>

    </div>
</div>
</x-app-layout>

