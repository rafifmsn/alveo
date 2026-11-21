<?php

namespace App\Http\Controllers;

use App\Models\ClubBroadcast;
use App\Models\SavedRoute;
use App\Models\User;
use App\Models\WorkoutLog;
use App\Services\DemoResetService;
use App\Services\IndonesiaLocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        protected IndonesiaLocationService $locations
    ) {}

    public function overview(): View
    {
        $totalUsers = User::count();
        $adminCount = User::where('role', User::ROLE_ADMIN)->count();
        $verifiedCount = User::whereNotNull('email_verified_at')->count();

        $totalRoutes = SavedRoute::count();
        $presetRoutes = SavedRoute::where('is_preset', true)->count();

        $totalWorkouts = WorkoutLog::count();
        $totalInhaledUg = round((float) WorkoutLog::sum('inhaled_dose_ug'), 1);
        $avgPm25 = round((float) (WorkoutLog::avg('ambient_pm2_5') ?? 0), 1);

        $totalBroadcasts = ClubBroadcast::count();
        $totalFollows = DB::table('club_broadcast_follows')->count();

        $cachedApiCalls = Schema::hasTable('cache') ? DB::table('cache')->count() : 0;
        $activeSessions = Schema::hasTable('sessions') ? DB::table('sessions')->count() : 0;

        return view('pages.admin.overview', [
            'stats' => [
                'users_total' => $totalUsers,
                'users_admins' => $adminCount,
                'users_athletes' => max(0, $totalUsers - $adminCount),
                'users_verified' => $verifiedCount,
                'routes_total' => $totalRoutes,
                'routes_presets' => $presetRoutes,
                'routes_custom' => max(0, $totalRoutes - $presetRoutes),
                'workouts_total' => $totalWorkouts,
                'workouts_inhaled_ug' => $totalInhaledUg,
                'workouts_avg_pm25' => $avgPm25,
                'broadcasts_total' => $totalBroadcasts,
                'broadcasts_follows' => $totalFollows,
                'cached_api_calls' => $cachedApiCalls,
                'locations_total' => $this->locations->count(),
                'active_sessions' => $activeSessions,
            ],
            'recentUsers' => User::query()
                ->latest()
                ->take(5)
                ->get(),
            'cacheDriver' => strtoupper((string) config('cache.default')),
            'cacheTtlMinutes' => (int) round(config('services.visualcrossing.cache_ttl', 3600) / 60),
            'visualCrossingConfigured' => config('services.visualcrossing.key', '') !== '',
        ]);
    }

    public function users(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $roleFilter = (string) $request->input('role', 'all');

        $query = User::query()
            ->withCount(['savedRoutes', 'workoutLogs', 'clubBroadcasts']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (in_array($roleFilter, [User::ROLE_ADMIN, User::ROLE_USER], true)) {
            $query->where('role', $roleFilter);
        }

        $users = $query
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.users', [
            'users' => $users,
            'search' => $search,
            'roleFilter' => $roleFilter,
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:user,admin'],
        ]);

        if ($user->id === $request->user()->id) {
            return back()->withErrors([
                'role' => 'You cannot change the role of your own active administrator account.',
            ]);
        }

        $user->role = $validated['role'];
        $user->save();

        $actionLabel = $validated['role'] === User::ROLE_ADMIN ? 'promoted to Administrator' : 'demoted to Athlete';

        return redirect()->route('admin.users')
            ->with('status', "{$user->name} has been {$actionLabel}.");
    }

    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors([
                'user' => 'You cannot delete your own active administrator account.',
            ]);
        }

        $deletedName = $user->name;

        DB::transaction(function () use ($user) {
            DB::table('club_broadcast_follows')->where('user_id', $user->id)->delete();
            ClubBroadcast::where('user_id', $user->id)->delete();
            SavedRoute::where('user_id', $user->id)->where('is_preset', false)->delete();
            WorkoutLog::where('user_id', $user->id)->delete();

            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }

            $user->delete();
        });

        return redirect()->route('admin.users')
            ->with('status', "Account for {$deletedName} and its personal records have been deleted.");
    }

    public function system(): View
    {
        $cachedApiCalls = Schema::hasTable('cache') ? DB::table('cache')->count() : 0;

        return view('pages.admin.system', [
            'dbConnection' => (string) config('database.default'),
            'cacheDriver' => strtoupper((string) config('cache.default')),
            'sessionDriver' => strtoupper((string) config('session.driver')),
            'cacheTtlMinutes' => (int) round(config('services.visualcrossing.cache_ttl', 3600) / 60),
            'cachedApiCalls' => $cachedApiCalls,
            'visualCrossingConfigured' => config('services.visualcrossing.key', '') !== '',
            'seedAdminEmail' => (string) config('app.seed.admin.email', 'admin@alveo.id'),
            'seedUserEmail' => (string) config('app.seed.user.email', 'rafif@alveo.id'),
        ]);
    }

    public function clearCache(): RedirectResponse
    {
        if (Schema::hasTable('cache')) {
            DB::table('cache')->delete();
        }
        Cache::flush();

        return redirect()->route('admin.system')
            ->with('status', 'Forecast cache cleared.');
    }

    public function resetDemo(Request $request, DemoResetService $resetService): RedirectResponse
    {
        $request->validate([
            'confirmation' => ['required', 'string', 'in:RESET'],
        ], [
            'confirmation.in' => 'Type RESET in uppercase to confirm wiping and re-seeding system data.',
        ]);

        $resetService->reset($request->user());

        return redirect()->route('admin.overview')
            ->with('status', 'System data has been reset to the clean seeded state.');
    }
}
