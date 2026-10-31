<x-app-layout title="Club Broadcasts">
<div x-data="{
        copiedId: null,
        copyDispatch(id, text) {
            navigator.clipboard.writeText(text);
            this.copiedId = id;
            setTimeout(() => { this.copiedId = null; }, 2200);
        }
    }"
    class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    <!-- Left Column: Publish Club Clearance Dispatch (5 cols) -->
    <div class="lg:col-span-5 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-2xs space-y-5">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                Publish Clean-Air Clearance
            </h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Select your club corridor and date. Alveo automatically attaches the cached optimal window and PM2.5 drop metrics.
            </p>
        </div>

        <form method="POST" action="{{ route('broadcasts.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                    Club / Community Name
                </label>
                <input type="text"
                       name="club_name"
                       required
                       value="{{ old('club_name', 'Senayan Dawn Runners') }}"
                       placeholder="e.g. Senayan Dawn Runners"
                       class="input input-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10" />
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                    Target Training Corridor
                </label>
                <select name="saved_route_id"
                        required
                        class="select select-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10 min-h-0">
                    @foreach ($corridors as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Sport</label>
                    <select name="activity_type" class="select select-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10 min-h-0">
                        <option value="running">Run</option>
                        <option value="cycling">Bike</option>
                        <option value="walking">Walk</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Duration</label>
                    <select name="duration_minutes" class="select select-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10 min-h-0 tabular-nums">
                        <option value="30">30m</option>
                        <option value="60" selected>60m</option>
                        <option value="90">90m</option>
                        <option value="120">120m</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Date</label>
                    <input type="date"
                           name="session_date"
                           required
                           value="{{ now()->toDateString() }}"
                           class="input input-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-xs rounded-xl h-10 tabular-nums" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                    Captain Briefing & Assembly Note
                </label>
                <textarea name="captain_note"
                          rows="3"
                          required
                          placeholder="e.g. Morning inversion clears by 09:00 AM. Meet at Gate 7 hydration point 10 minutes prior."
                          class="textarea textarea-bordered w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl"></textarea>
            </div>

            <button type="submit"
                    class="btn w-full bg-forest-900 hover:bg-forest-800 text-white border-0 rounded-xl font-semibold text-sm h-11 shadow-xs inline-flex items-center justify-center gap-2">
                <x-icon name="megaphone" class="w-4 h-4" />
                <span>Publish Club Clearance Card</span>
            </button>
        </form>
    </div>

    <!-- Right Column: Club Broadcast Feed (7 cols) -->
    <div class="lg:col-span-7 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                Active Club Clearances ({{ $broadcasts->count() }})
            </h3>
            <span class="text-xs text-slate-500 dark:text-slate-400">
                Follow clubs to track group window shifts
            </span>
        </div>

        @foreach ($broadcasts as $broadcast)
            @php
                $isFollowing = in_array($broadcast->id, $followedIds, true);
                $tgShareUrl = 'https://t.me/share/url?' . http_build_query([
                    'url' => config('app.url'),
                    'text' => $broadcast->telegramText(),
                ]);
            @endphp
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-2xs space-y-4">
                <!-- Top Club & Follow Row -->
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                    <div>
                        <span class="text-base font-bold text-slate-900 dark:text-white block">
                            {{ $broadcast->club_name }}
                        </span>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Captain: <strong class="text-slate-700 dark:text-slate-300">{{ $broadcast->captain_name }}</strong>
                            • {{ $broadcast->session_date->format('D, d M Y') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <form method="POST" action="{{ route('broadcasts.follow', $broadcast) }}">
                            @csrf
                            <button type="submit"
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold border transition inline-flex items-center gap-1.5 whitespace-nowrap tabular-nums {{ $isFollowing
                                        ? 'bg-forest-900 text-white border-forest-900'
                                        : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-700 hover:border-forest-700' }}">
                                <x-icon name="users" class="w-3.5 h-3.5 shrink-0" />
                                <span>{{ $isFollowing ? 'Following' : 'Follow' }} ({{ $broadcast->followers_count }})</span>
                            </button>
                        </form>

                        @if (auth()->id() === $broadcast->user_id)
                            <form method="POST" action="{{ route('broadcasts.destroy', $broadcast) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        title="Delete Broadcast"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50 transition">
                                    <x-icon name="trash-2" class="w-4 h-4" />
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Metrics Strip -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 tabular-nums">
                    <div>
                        <p class="text-[11px] text-slate-500">Corridor & Session</p>
                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $broadcast->corridor_name }}</p>
                        <p class="text-[11px] text-slate-500">{{ ucfirst($broadcast->activity_type) }} • {{ $broadcast->duration_minutes }}m</p>
                    </div>

                    <div>
                        <p class="text-[11px] text-slate-500">Cleared Window</p>
                        <p class="text-sm font-extrabold text-forest-800 dark:text-forest-300">
                            {{ $broadcast->window_start }} - {{ $broadcast->window_end }}
                        </p>
                        <p class="text-[11px] text-slate-500">{{ $broadcast->wind_summary }}</p>
                    </div>

                    <div>
                        <p class="text-[11px] text-slate-500">Projected Exposure</p>
                        <p class="text-sm font-extrabold text-slate-900 dark:text-white">
                            {{ $broadcast->projected_pm2_5 }} µg/m³
                        </p>
                        <p class="text-[11px] font-semibold text-forest-700 dark:text-forest-400">
                            -{{ round($broadcast->improvement_pct) }}% inhaled PM2.5
                        </p>
                    </div>
                </div>

                <!-- Captain Note -->
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    {{ $broadcast->captain_note }}
                </p>

                <!-- Footer Share Actions -->
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <button type="button"
                                @click="copyDispatch({{ $broadcast->id }}, @js($broadcast->telegramText()))"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-forest-50 hover:text-forest-900 transition inline-flex items-center gap-1.5">
                            <x-icon name="copy" class="w-3.5 h-3.5" />
                            <span x-text="copiedId === {{ $broadcast->id }} ? 'Copied!' : 'Copy Dispatch Text'">Copy Dispatch Text</span>
                        </button>

                        <a href="{{ $tgShareUrl }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-forest-50 dark:bg-forest-950 text-forest-800 dark:text-forest-300 hover:bg-forest-100 transition inline-flex items-center gap-1.5">
                            <x-icon name="send" class="w-3.5 h-3.5" />
                            <span>Share to Telegram</span>
                        </a>
                    </div>

                    <a href="{{ route('finder', ['location' => $broadcast->corridor_name, 'lat' => $broadcast->latitude, 'lon' => $broadcast->longitude, 'activity' => $broadcast->activity_type, 'duration' => $broadcast->duration_minutes]) }}"
                       class="text-xs font-semibold text-forest-800 dark:text-forest-300 hover:underline inline-flex items-center gap-1">
                        <span>Inspect in Window Finder</span>
                        <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                    </a>
                </div>
            </div>
        @endforeach
    </div>

</div>
</x-app-layout>

