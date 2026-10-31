<x-app-layout title="Saved Routes &amp; Hotspots">
<div x-data="{ showCreateModal: false }" class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xs">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                Athletic Corridors &amp; Monitored Loops
            </h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Inspect cached air quality across Greater Jakarta training loops and launch one-click window evaluations.
            </p>
        </div>

        <button type="button"
                @click="showCreateModal = !showCreateModal"
                class="btn bg-forest-900 hover:bg-forest-800 text-white border-0 rounded-xl font-semibold text-sm h-11 px-5 shadow-xs inline-flex items-center gap-2 shrink-0">
            <x-icon name="plus" class="w-4 h-4" />
            <span>Save Custom Corridor</span>
        </button>
    </div>

    <!-- Collapsible Add Corridor Form Card -->
    <div x-show="showCreateModal"
         x-cloak
         class="bg-white dark:bg-slate-900 border border-forest-300 dark:border-forest-800 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                Add Custom Training Corridor
            </h3>
            <button type="button" @click="showCreateModal = false" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                Cancel
            </button>
        </div>

        <form method="POST" action="{{ route('routes.store') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Corridor Name</label>
                <input type="text" name="name" required placeholder="e.g. Epicentrum Walk Loop"
                       class="input input-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10" />
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Region / District</label>
                <input type="text" name="region" required placeholder="e.g. Kuningan, South Jakarta"
                       class="input input-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10" />
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Surface Type</label>
                <input type="text" name="surface_type" required value="Protected Road Loop"
                       class="input input-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10" />
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Latitude</label>
                <input type="number" step="0.0001" name="latitude" required value="-6.2210"
                       class="input input-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10 tabular-nums" />
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Longitude</label>
                <input type="number" step="0.0001" name="longitude" required value="106.8320"
                       class="input input-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10 tabular-nums" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Loop Distance (km)</label>
                    <input type="number" step="0.1" name="distance_km" required value="5.0"
                           class="input input-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10 tabular-nums" />
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Primary Sport</label>
                    <select name="preferred_activity" class="select select-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10 min-h-0">
                        <option value="running">Running</option>
                        <option value="cycling">Cycling</option>
                        <option value="walking">Walking</option>
                    </select>
                </div>
            </div>

            <div class="sm:col-span-2 lg:col-span-3 flex justify-end pt-2">
                <button type="submit"
                        class="btn bg-forest-900 hover:bg-forest-800 text-white border-0 rounded-xl font-semibold text-sm h-10 px-6">
                    Save Corridor to Database
                </button>
            </div>
        </form>
    </div>

    <!-- Corridors Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach ($enrichedRoutes as $item)
            @php
                /** @var \App\Models\SavedRoute $route */
                $route = $item['model'];
                $eval = $item['eval'];
                $cur = $eval['current_conditions'];
                $rec = $eval['recommendation'];
                $isGo = $eval['status'] === 'GO';
            @endphp
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-2xs flex flex-col justify-between space-y-5">
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-semibold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                    {{ ucfirst($route->preferred_activity) }} • {{ $route->distance_km }} km
                                </span>
                                @if ($route->is_preset)
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-forest-50 dark:bg-forest-950 text-forest-800 dark:text-forest-300">
                                        Verified Hotspot
                                    </span>
                                @endif
                            </div>
                            <h3 class="mt-2 text-base font-bold text-slate-900 dark:text-white">
                                {{ $route->name }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $route->region }} • {{ $route->surface_type }}
                            </p>
                        </div>

                        @if (! $route->is_preset)
                            <form method="POST" action="{{ route('routes.destroy', $route) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        title="Remove Corridor"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50 transition">
                                    <x-icon name="trash-2" class="w-4 h-4" />
                                </button>
                            </form>
                        @endif
                    </div>

                    <!-- Cached Environmental Metrics Grid -->
                    <div class="grid grid-cols-2 gap-3 p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 tabular-nums">
                        <div>
                            <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Current PM2.5</p>
                            <p class="text-lg font-bold text-slate-900 dark:text-white">
                                {{ $cur['pm2_5'] }} <span class="text-xs font-normal text-slate-500">µg/m³</span>
                            </p>
                            <p class="text-[11px] text-slate-500">
                                AQI {{ $cur['us_aqi'] }} • DEFRA {{ $cur['gb_defra_index'] }}/10
                            </p>
                        </div>

                        <div>
                            <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Optimal Window</p>
                            <p class="text-lg font-bold text-forest-800 dark:text-forest-300">
                                {{ $rec['short_window'] }}
                            </p>
                            <p class="text-[11px] font-semibold text-forest-700 dark:text-forest-400">
                                {{ $rec['projected_pm2_5'] }} µg/m³ (-{{ round($rec['improvement_pct']) }}%)
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 tabular-nums">
                        <span>Wind: <strong class="text-slate-700 dark:text-slate-200">{{ $cur['wind_speed_kmh'] }} km/h {{ $cur['wind_dir'] }}</strong></span>
                        <span>Coords: {{ number_format($route->latitude, 3) }}, {{ number_format($route->longitude, 3) }}</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center gap-2.5">
                    <a href="{{ route('finder', ['route_id' => $route->id]) }}"
                       class="btn flex-1 bg-forest-900 hover:bg-forest-800 text-white border-0 rounded-xl font-semibold text-xs h-10 min-h-0 inline-flex items-center justify-center gap-1.5">
                        <x-icon name="compass" class="w-3.5 h-3.5" />
                        <span>Evaluate Window</span>
                    </a>

                    <a href="{{ route('dosimetry.index', ['route_id' => $route->id]) }}"
                       class="btn btn-outline border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-xl font-semibold text-xs h-10 min-h-0 px-3.5">
                        Dosimetry
                    </a>
                </div>
            </div>
        @endforeach
    </div>

</div>
</x-app-layout>

