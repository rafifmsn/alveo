<x-app-layout title="User Management">
<div class="space-y-6">

    <!-- Search & Role Filter -->
    <form method="GET" action="{{ route('admin.users') }}" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-2xs flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
        <div class="relative flex-1 max-w-md">
            <span class="absolute inset-y-0 left-3.5 flex items-center text-slate-400 pointer-events-none">
                <x-icon name="search" class="w-4 h-4" />
            </span>
            <input type="text"
                   name="q"
                   value="{{ $search }}"
                   placeholder="Search by name or email..."
                   class="input input-bordered w-full pl-10 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10" />
        </div>

        <div class="flex items-center gap-2.5">
            <select name="role" class="select select-bordered bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-sm rounded-xl h-10 min-h-0">
                <option value="all" @selected($roleFilter === 'all')>All Roles</option>
                <option value="admin" @selected($roleFilter === 'admin')>Administrators</option>
                <option value="user" @selected($roleFilter === 'user')>Athletes</option>
            </select>

            <button type="submit"
                    class="btn bg-forest-900 hover:bg-forest-800 text-white border-0 rounded-xl font-semibold text-xs h-10 min-h-0 px-5">
                Filter
            </button>

            @if ($search !== '' || $roleFilter !== 'all')
                <a href="{{ route('admin.users') }}"
                   class="btn btn-ghost text-xs h-10 min-h-0 rounded-xl text-slate-500">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <!-- Users Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-slate-500 bg-slate-50/70 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                        <th>User</th>
                        <th>Role</th>
                        <th class="tabular-nums">Activity Counts</th>
                        <th>Joined</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $managedUser)
                        @php
                            $isSelf = $managedUser->id === auth()->id();
                        @endphp
                        <tr class="border-b border-slate-100 dark:border-slate-800/60">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-forest-100 dark:bg-forest-900/70 border border-forest-300 dark:border-forest-700 flex items-center justify-center text-forest-900 dark:text-forest-200 font-bold text-xs shrink-0">
                                        {{ strtoupper(substr($managedUser->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-900 dark:text-white text-sm truncate">
                                            {{ $managedUser->name }}
                                            @if ($isSelf)
                                                <span class="ml-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">You</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $managedUser->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $managedUser->isAdmin()
                                    ? 'bg-forest-100 dark:bg-forest-950 text-forest-900 dark:text-forest-300 border border-forest-300 dark:border-forest-800'
                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                                    {{ $managedUser->isAdmin() ? 'Admin' : 'Athlete' }}
                                </span>
                            </td>
                            <td class="text-xs text-slate-600 dark:text-slate-400 tabular-nums">
                                {{ $managedUser->saved_routes_count }} routes •
                                {{ $managedUser->workout_logs_count }} logs •
                                {{ $managedUser->club_broadcasts_count }} broadcasts
                            </td>
                            <td class="text-xs text-slate-500 dark:text-slate-400 tabular-nums">
                                {{ $managedUser->created_at?->format('d M Y') }}
                            </td>
                            <td class="text-right">
                                @if ($isSelf)
                                    <span class="text-xs font-medium text-slate-400 dark:text-slate-500">Current Session</span>
                                @else
                                    <div class="inline-flex items-center justify-end gap-2 flex-nowrap">
                                        <form method="POST" action="{{ route('admin.users.role', $managedUser) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="role" value="{{ $managedUser->isAdmin() ? 'user' : 'admin' }}" />
                                            <button type="submit"
                                                    class="btn btn-xs rounded-lg font-semibold h-8 min-h-0 px-3 whitespace-nowrap {{ $managedUser->isAdmin()
                                                        ? 'btn-outline border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800'
                                                        : 'bg-forest-900 hover:bg-forest-800 text-white border-0' }}">
                                                {{ $managedUser->isAdmin() ? 'Demote' : 'Promote' }}
                                            </button>
                                        </form>

                                        <form method="POST"
                                              action="{{ route('admin.users.destroy', $managedUser) }}"
                                              onsubmit="return confirm('Delete account for {{ addslashes($managedUser->email) }}? This permanently removes their routes, workouts, and broadcasts.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-xs btn-outline border-red-200 dark:border-red-900/60 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/50 rounded-lg font-semibold h-8 min-h-0 px-3 whitespace-nowrap">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-sm text-slate-500 py-10">
                                No accounts match the current search or filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
</x-app-layout>
