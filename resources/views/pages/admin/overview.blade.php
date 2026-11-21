<x-app-layout title="Admin Overview">
<div class="space-y-6">

    <!-- Top Action Strip -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-2xs">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">System Aggregate Telemetry</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Platform-wide counts across accounts, corridors, exposure logs, broadcasts, and forecast cache.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.users') }}"
               class="btn btn-sm bg-forest-900 hover:bg-forest-800 text-white border-0 rounded-xl px-4 h-10 font-semibold text-xs inline-flex items-center gap-2">
                <x-icon name="users" class="w-4 h-4" />
                <span>Manage Users</span>
            </a>
            <a href="{{ route('admin.system') }}"
               class="btn btn-sm btn-outline border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl px-4 h-10 font-semibold text-xs inline-flex items-center gap-2">
                <x-icon name="database" class="w-4 h-4" />
                <span>System & Reset</span>
            </a>
        </div>
    </div>

    <!-- Primary Aggregate Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 tabular-nums">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">
                <span>Registered Users</span>
                <x-icon name="users" class="w-4 h-4 text-forest-700 dark:text-forest-400" />
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white">{{ number_format($stats['users_total']) }}</p>
            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                {{ $stats['users_admins'] }} admin • {{ $stats['users_athletes'] }} athlete • {{ $stats['users_verified'] }} verified
            </p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">
                <span>Cached API Calls</span>
                <x-icon name="database" class="w-4 h-4 text-forest-700 dark:text-forest-400" />
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white">{{ number_format($stats['cached_api_calls']) }}</p>
            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                {{ $cacheDriver }} store • {{ $cacheTtlMinutes }} min TTL
            </p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">
                <span>Indexed Locations</span>
                <x-icon name="map-pin" class="w-4 h-4 text-forest-700 dark:text-forest-400" />
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white">{{ number_format($stats['locations_total']) }}</p>
            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                {{ $stats['routes_presets'] }} preset corridors • {{ $stats['routes_custom'] }} custom routes
            </p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">
                <span>Club Broadcasts</span>
                <x-icon name="megaphone" class="w-4 h-4 text-forest-700 dark:text-forest-400" />
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white">{{ number_format($stats['broadcasts_total']) }}</p>
            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                {{ number_format($stats['broadcasts_follows']) }} active follow links
            </p>
        </div>
    </div>

    <!-- Secondary Operational Metrics & Recent Accounts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 tabular-nums">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-2xs space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Exposure & Runtime Summary</h3>
            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700">
                    <span class="text-slate-500 dark:text-slate-400">Logged Workouts</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ number_format($stats['workouts_total']) }} sessions</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700">
                    <span class="text-slate-500 dark:text-slate-400">Aggregate Inhaled Dose</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ number_format($stats['workouts_inhaled_ug'], 1) }} µg</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700">
                    <span class="text-slate-500 dark:text-slate-400">Mean Ambient PM2.5</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ number_format($stats['workouts_avg_pm25'], 1) }} µg/m³</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700">
                    <span class="text-slate-500 dark:text-slate-400">Active Browser Sessions</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ number_format($stats['active_sessions']) }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700">
                    <span class="text-slate-500 dark:text-slate-400">Forecast Engine</span>
                    <span class="font-bold text-forest-800 dark:text-forest-300">
                        {{ $visualCrossingConfigured ? 'Live API Key Active' : 'Deterministic Engine Active' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-2xs space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Recent Accounts</h3>
                <a href="{{ route('admin.users') }}" class="text-xs font-semibold text-forest-800 dark:text-forest-300 hover:underline">
                    View all users
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr class="text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200 dark:border-slate-800">
                            <th>Account</th>
                            <th>Role</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentUsers as $recentUser)
                            <tr class="border-b border-slate-100 dark:border-slate-800/60">
                                <td>
                                    <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $recentUser->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $recentUser->email }}</p>
                                </td>
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $recentUser->isAdmin()
                                        ? 'bg-forest-100 dark:bg-forest-950 text-forest-900 dark:text-forest-300 border border-forest-300 dark:border-forest-800'
                                        : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                                        {{ ucfirst($recentUser->role) }}
                                    </span>
                                </td>
                                <td class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $recentUser->created_at?->format('d M Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-xs text-slate-500 py-6">No accounts registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</x-app-layout>
