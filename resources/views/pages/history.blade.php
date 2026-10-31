<x-app-layout title="Exposure History &amp; Training Log">
<div x-data="{ showLogForm: false }" class="space-y-6">

    <!-- Top 4 Weekly Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 tabular-nums">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-2xs">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">7-Day Retained PM2.5</p>
            <p class="mt-1.5 text-2xl font-bold text-slate-900 dark:text-white">
                {{ $weeklyStats['dose_ug'] }} <span class="text-sm font-medium text-slate-500">µg</span>
            </p>
            <p class="mt-1 text-xs text-forest-800 dark:text-forest-300 font-semibold">
                Across {{ $weeklyStats['air_volume_m3'] }} m³ air ventilated
            </p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-2xs">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Mean Session PM2.5</p>
            <p class="mt-1.5 text-2xl font-bold text-slate-900 dark:text-white">
                {{ $weeklyStats['avg_pm2_5'] }} <span class="text-sm font-medium text-slate-500">µg/m³</span>
            </p>
            <p class="mt-1 text-xs text-slate-500">
                Athletic target: ≤ 35.0 µg/m³
            </p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-2xs">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">7-Day Training Volume</p>
            <p class="mt-1.5 text-2xl font-bold text-slate-900 dark:text-white">
                {{ $weeklyStats['minutes'] }} <span class="text-sm font-medium text-slate-500">mins</span>
            </p>
            <p class="mt-1 text-xs text-slate-500">
                {{ $weeklyStats['sessions_count'] }} logged outdoor sessions
            </p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">WHO Reference Load</p>
                <span class="text-xs font-bold text-forest-800 dark:text-forest-300">{{ $weeklyStats['ceiling_pct'] }}%</span>
            </div>
            <div class="mt-2.5 h-2.5 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                <div class="h-full rounded-full {{ $weeklyStats['ceiling_pct'] > 85 ? 'bg-amber-500' : 'bg-forest-800' }}"
                     style="width: {{ $weeklyStats['ceiling_pct'] }}%"></div>
            </div>
            <p class="mt-2 text-[11px] text-slate-500">
                Reference ceiling: {{ $weeklyStats['who_ceiling_ug'] }} µg
            </p>
        </div>
    </div>

    <!-- Header & Log Session Trigger -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xs">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                Recorded Outdoor Sessions
            </h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Log completed workouts to track cumulative particulate inhalation calculated deterministically from corridor snapshots.
            </p>
        </div>

        <button type="button"
                @click="showLogForm = !showLogForm"
                class="btn bg-forest-900 hover:bg-forest-800 text-white border-0 rounded-xl font-semibold text-sm h-11 px-5 shadow-xs inline-flex items-center gap-2 shrink-0">
            <x-icon name="plus" class="w-4 h-4" />
            <span>Log Completed Workout</span>
        </button>
    </div>

    <!-- Collapsible Log Workout Form -->
    <div x-show="showLogForm"
         x-cloak
         class="bg-white dark:bg-slate-900 border border-forest-300 dark:border-forest-800 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                Log Outdoor Session
            </h3>
            <button type="button" @click="showLogForm = false" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                Cancel
            </button>
        </div>

        <form method="POST" action="{{ route('history.store') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @csrf

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Corridor</label>
                <select name="saved_route_id" class="select select-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10 min-h-0">
                    @foreach ($corridors as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Activity</label>
                <select name="activity_type" class="select select-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10 min-h-0">
                    <option value="running">Running</option>
                    <option value="cycling">Cycling</option>
                    <option value="walking">Walking</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Exertion Zone</label>
                <select name="exertion_zone" class="select select-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10 min-h-0">
                    @foreach ($zonesConfig as $zNum => $zData)
                        @if ($zNum > 0)
                            <option value="{{ $zNum }}" @selected($zNum === 3)>{{ $zData['short'] }} ({{ $zData['ventilation_lpm'] }} L/m)</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Duration (Minutes)</label>
                <input type="number" name="duration_minutes" required value="60" min="10" max="360"
                       class="input input-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10 tabular-nums" />
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Ambient PM2.5 (µg/m³)</label>
                <input type="number" step="0.1" name="ambient_pm2_5" placeholder="Auto from corridor cache"
                       class="input input-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10 tabular-nums" />
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Session Date</label>
                <input type="date" name="trained_date" required value="{{ now()->toDateString() }}"
                       class="input input-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10 tabular-nums" />
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Start Time</label>
                <input type="time" name="trained_time" required value="09:00"
                       class="input input-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10 tabular-nums" />
            </div>

            <div class="sm:col-span-2 lg:col-span-3">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Session Notes (Optional)</label>
                <input type="text" name="notes" placeholder="e.g. Post-inversion window; light coastal breeze"
                       class="input input-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10" />
            </div>

            <div class="flex items-end justify-end">
                <button type="submit"
                        class="btn w-full bg-forest-900 hover:bg-forest-800 text-white border-0 rounded-xl font-semibold text-sm h-10">
                    Calculate &amp; Save Log
                </button>
            </div>
        </form>
    </div>

    <!-- Chronological Exposure Log Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="table w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200/80 dark:border-slate-800 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/60 dark:bg-slate-800/40">
                        <th class="py-3.5 px-5">Date &amp; Window</th>
                        <th class="py-3.5 px-5">Corridor &amp; Activity</th>
                        <th class="py-3.5 px-5">Ventilation</th>
                        <th class="py-3.5 px-5">Ambient PM2.5</th>
                        <th class="py-3.5 px-5">Retained Dose</th>
                        <th class="py-3.5 px-5">Risk Score</th>
                        <th class="py-3.5 px-5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800 tabular-nums">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                            <td class="py-4 px-5 whitespace-nowrap">
                                <p class="font-bold text-slate-900 dark:text-white">{{ $log->trained_at->format('d M Y') }}</p>
                                <p class="text-xs text-slate-500">{{ $log->trained_at->format('H:i') }} WIB • {{ $log->duration_minutes }}m</p>
                            </td>
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold uppercase bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $log->activity_type }}
                                    </span>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $log->location_name }}</span>
                                </div>
                                @if ($log->notes)
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $log->notes }}</p>
                                @endif
                            </td>
                            <td class="py-4 px-5 whitespace-nowrap">
                                <p class="font-semibold text-slate-800 dark:text-slate-200">Zone {{ $log->exertion_zone }} ({{ $log->ventilation_rate_lpm }} L/m)</p>
                                <p class="text-xs text-slate-500">{{ $log->air_volume_m3 }} m³ air</p>
                            </td>
                            <td class="py-4 px-5 whitespace-nowrap">
                                <span class="font-bold {{ $log->ambient_pm2_5 <= 35 ? 'text-forest-800 dark:text-forest-300' : 'text-amber-700 dark:text-amber-400' }}">
                                    {{ $log->ambient_pm2_5 }} µg/m³
                                </span>
                            </td>
                            <td class="py-4 px-5 whitespace-nowrap">
                                <span class="text-base font-extrabold text-slate-900 dark:text-white">
                                    {{ $log->inhaled_dose_ug }} µg
                                </span>
                            </td>
                            <td class="py-4 px-5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $log->exertion_risk_score <= 4.0
                                    ? 'bg-forest-50 dark:bg-forest-950 text-forest-800 dark:text-forest-300 border border-forest-200 dark:border-forest-800'
                                    : 'bg-amber-50 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800' }}">
                                    {{ $log->exertion_risk_score }} / 10
                                </span>
                            </td>
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <form method="POST" action="{{ route('history.destroy', $log) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            title="Delete Log"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50 transition">
                                        <x-icon name="trash-2" class="w-4 h-4" />
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-500">
                                No outdoor sessions logged yet. Click "Log Completed Workout" above to record your first session.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
</x-app-layout>

