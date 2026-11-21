<x-app-layout title="System & Reset">
<div class="max-w-4xl space-y-6">

    <!-- Runtime Configuration Summary -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-2xs space-y-4 tabular-nums">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Runtime & Seeding Configuration</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Active storage backends and environment-derived seeded accounts.
                </p>
            </div>
            <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-forest-50 dark:bg-forest-950 text-forest-800 dark:text-forest-300 shrink-0">
                {{ $visualCrossingConfigured ? 'Live API Key Active' : 'Deterministic Engine Active' }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700">
                <p class="text-slate-500">Database Connection</p>
                <p class="mt-0.5 font-bold text-slate-900 dark:text-white">{{ strtoupper($dbConnection) }}</p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700">
                <p class="text-slate-500">Cache & Session Drivers</p>
                <p class="mt-0.5 font-bold text-slate-900 dark:text-white">{{ $cacheDriver }} / {{ $sessionDriver }}</p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700">
                <p class="text-slate-500">Cached Forecast Cells</p>
                <p class="mt-0.5 font-bold text-slate-900 dark:text-white">{{ number_format($cachedApiCalls) }} ({{ $cacheTtlMinutes }}m TTL)</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700">
                <p class="text-slate-500">Env-Seeded Admin</p>
                <p class="mt-0.5 font-bold text-slate-900 dark:text-white">{{ $seedAdminEmail }}</p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700">
                <p class="text-slate-500">Env-Seeded Athlete</p>
                <p class="mt-0.5 font-bold text-slate-900 dark:text-white">{{ $seedUserEmail }}</p>
            </div>
        </div>
    </div>

    <!-- Forecast Cache Maintenance -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Clear Cached Forecast Responses</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Flush all stored Visual Crossing / deterministic forecast entries from the cache store without touching user data.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.system.clear-cache') }}">
            @csrf
            <button type="submit"
                    class="btn btn-outline border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl font-semibold text-xs h-10 px-5 inline-flex items-center gap-2 shrink-0">
                <x-icon name="refresh-cw" class="w-4 h-4" />
                <span>Clear Cache</span>
            </button>
        </form>
    </div>

    <!-- Destructive In-App Reset -->
    <div class="bg-white dark:bg-slate-900 border border-red-200 dark:border-red-900/70 rounded-2xl p-6 shadow-2xs space-y-4">
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Reset System Data to Seeded State</h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                Deletes all non-active user accounts, custom routes, workout logs, club broadcasts, follow links, and cached API entries, then re-runs the environment seeder (3 preset corridors, 1 verified athlete, 3 workout logs, and 1 broadcast with 1 follow). Your current administrator session remains active.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.system.reset') }}" class="flex flex-col sm:flex-row sm:items-end gap-3 pt-2">
            @csrf
            <div class="flex-1 max-w-xs">
                <label for="confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                    Type RESET to confirm
                </label>
                <input id="confirmation"
                       type="text"
                       name="confirmation"
                       placeholder="RESET"
                       required
                       autocomplete="off"
                       class="input input-bordered w-full bg-slate-50 dark:bg-slate-800 border-red-200 dark:border-red-900/60 text-sm rounded-xl h-10" />
            </div>

            <button type="submit"
                    class="btn bg-red-600 hover:bg-red-700 text-white border-0 rounded-xl font-semibold text-xs h-10 px-5 inline-flex items-center gap-2">
                <x-icon name="trash-2" class="w-4 h-4" />
                <span>Reset System Data</span>
            </button>
        </form>
    </div>

</div>
</x-app-layout>
