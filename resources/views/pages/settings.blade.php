<x-app-layout title="Preferences & Settings">
<div class="max-w-4xl space-y-6">

    <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Card 1: Athlete Profile & Verification Status -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-2xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">
                        Athlete Profile & Account Clearance
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Manage your identity and account credentials.
                    </p>
                </div>

                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-forest-50 dark:bg-forest-950 text-forest-800 dark:text-forest-300 border border-forest-200 dark:border-forest-800 tabular-nums">
                    <x-icon name="check-circle" class="w-3.5 h-3.5" />
                    <span>Verified ({{ $user->email_verified_at?->format('d M Y') }})</span>
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Display Name
                    </label>
                    <input type="text"
                           name="name"
                           required
                           value="{{ old('name', $user->name) }}"
                           class="input input-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10" />
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Registered Email (Read-only)
                    </label>
                    <input type="email"
                           disabled
                           value="{{ $user->email }}"
                           class="input input-bordered w-full bg-slate-100 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 text-slate-500 text-sm rounded-xl h-10" />
                </div>
            </div>
        </div>

        <!-- Card 2: Atmospheric Sensitivity & Workout Defaults -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-2xs space-y-5">
            <div class="pb-4 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">
                    Threshold Calibration & Session Defaults
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Configure the PM2.5 safety ceiling used by the Window Finder to trigger GO vs POSTPONE verdicts.
                </p>
            </div>

            <!-- Sensitivity Profile Radio Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 tabular-nums">
                @php
                    $profiles = [
                        'sensitive' => ['label' => 'Sensitive / Asthmatic', 'limit' => '≤ 25.0 µg/m³', 'desc' => 'Strict airway protection for reactive bronchioles.'],
                        'standard' => ['label' => 'Athletic Standard', 'limit' => '≤ 35.0 µg/m³', 'desc' => 'Recommended ceiling for high-ventilation endurance sessions.'],
                        'tolerant' => ['label' => 'High Tolerance', 'limit' => '≤ 50.0 µg/m³', 'desc' => 'Relaxed threshold for short or low-intensity urban commutes.'],
                    ];
                @endphp
                @foreach ($profiles as $pKey => $pMeta)
                    <label class="cursor-pointer border rounded-xl p-4 transition block {{ $user->sensitivity_profile === $pKey
                        ? 'bg-forest-50/70 dark:bg-forest-950/60 border-forest-700 dark:border-forest-600'
                        : 'bg-slate-50/60 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700' }}">
                        <div class="flex items-center justify-between mb-1.5">
                            <input type="radio"
                                   name="sensitivity_profile"
                                   value="{{ $pKey }}"
                                   @checked($user->sensitivity_profile === $pKey)
                                   class="radio radio-xs radio-primary" />
                            <span class="text-xs font-extrabold text-forest-800 dark:text-forest-300">{{ $pMeta['limit'] }}</span>
                        </div>
                        <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $pMeta['label'] }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">{{ $pMeta['desc'] }}</p>
                    </label>
                @endforeach
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Default Sport
                    </label>
                    <select name="default_activity" class="select select-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10 min-h-0">
                        <option value="running" @selected($user->default_activity === 'running')>Running</option>
                        <option value="cycling" @selected($user->default_activity === 'cycling')>Cycling</option>
                        <option value="walking" @selected($user->default_activity === 'walking')>Walking</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Default Duration
                    </label>
                    <select name="default_duration" class="select select-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10 min-h-0 tabular-nums">
                        @foreach ([30, 60, 90, 120] as $dOpt)
                            <option value="{{ $dOpt }}" @selected($user->default_duration === $dOpt)>{{ $dOpt }} minutes</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Measurement Units
                    </label>
                    <select name="unit_system" class="select select-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10 min-h-0">
                        <option value="metric" @selected($user->unit_system === 'metric')>Metric (km/h, °C)</option>
                        <option value="imperial" @selected($user->unit_system === 'imperial')>Imperial (mph, °F)</option>
                    </select>
                </div>
            </div>

            <!-- Dark Mode Toggle -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-900 dark:text-white">Dark Theme Appearance</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Use low-glare dark slate surfaces for pre-dawn session checks.</p>
                </div>
                <input type="checkbox"
                       name="dark_mode"
                       value="1"
                       @checked($user->dark_mode)
                       @change="darkMode = $event.target.checked; localStorage.setItem('alveo_dark', darkMode)"
                       class="toggle toggle-primary" />
            </div>
        </div>

        <!-- Card 3: Optional Password Update -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-2xs space-y-4">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">
                    Update Password (Optional)
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Leave blank if you do not wish to change your current password.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">New Password</label>
                    <input type="password" name="new_password" autocomplete="new-password"
                           class="input input-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10" />
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" autocomplete="new-password"
                           class="input input-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10" />
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit"
                        class="btn bg-forest-900 hover:bg-forest-800 text-white border-0 rounded-xl font-semibold text-sm h-11 px-6 shadow-xs">
                    Save All Preferences
                </button>
            </div>
        </div>
    </form>

    <!-- Card 4: Database Cache & WeatherAPI Telemetry -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-2xs space-y-4 tabular-nums">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div class="flex items-center gap-2.5">
                <x-icon name="database" class="w-5 h-5 text-forest-700 dark:text-forest-400 shrink-0" />
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                    WeatherAPI Quota Conservation & Database Cache
                </h3>
            </div>
            <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold bg-forest-50 dark:bg-forest-950 text-forest-800 dark:text-forest-300 shrink-0">
                {{ $weatherApiConfigured ? 'Live API Key Active' : 'Deterministic Engine Active' }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700">
                <p class="text-slate-500">Cache Driver</p>
                <p class="mt-0.5 font-bold text-slate-900 dark:text-white">{{ strtoupper(config('cache.default')) }} (No Redis)</p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700">
                <p class="text-slate-500">Snapshot TTL & Grid</p>
                <p class="mt-0.5 font-bold text-slate-900 dark:text-white">{{ $cacheTtlMinutes }} mins • 0.01° (~1.1 km)</p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700">
                <p class="text-slate-500">Active DB Cache Rows</p>
                <p class="mt-0.5 font-bold text-slate-900 dark:text-white">{{ $cacheEntriesCount }} cached cells</p>
            </div>
        </div>
    </div>

    <!-- Card 5: Account Sign Out -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">End Active Session</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Sign out of your Alveo athlete account on this browser.</p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="btn btn-outline border-red-200 dark:border-red-900/60 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/50 hover:border-red-300 rounded-xl font-semibold text-xs h-10 px-5 inline-flex items-center gap-2">
                <x-icon name="log-out" class="w-4 h-4" />
                <span>Log Out</span>
            </button>
        </form>
    </div>

</div>
</x-app-layout>

