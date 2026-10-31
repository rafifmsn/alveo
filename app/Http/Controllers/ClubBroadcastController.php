<?php

namespace App\Http\Controllers;

use App\Models\ClubBroadcast;
use App\Models\SavedRoute;
use App\Services\ExposureEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClubBroadcastController extends Controller
{
    public function __construct(
        protected ExposureEngine $engine
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $corridors = SavedRoute::query()
            ->where(function ($q) use ($user) {
                $q->where('is_preset', true);
                if ($user) {
                    $q->orWhere('user_id', $user->id);
                }
            })
            ->orderByDesc('is_preset')
            ->orderBy('name')
            ->get();

        $broadcasts = ClubBroadcast::query()
            ->orderByDesc('session_date')
            ->orderByDesc('created_at')
            ->get();

        $followedIds = $user
            ? DB::table('club_broadcast_follows')
                ->where('user_id', $user->id)
                ->pluck('club_broadcast_id')
                ->toArray()
            : [];

        return view('pages.broadcasts', [
            'broadcasts' => $broadcasts,
            'corridors' => $corridors,
            'followedIds' => $followedIds,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'club_name' => ['required', 'string', 'max:120'],
            'saved_route_id' => ['required', 'integer', 'exists:saved_routes,id'],
            'activity_type' => ['required', 'string', 'in:running,cycling,walking'],
            'duration_minutes' => ['required', 'integer', 'in:30,60,90,120'],
            'session_date' => ['required', 'date'],
            'captain_note' => ['required', 'string', 'max:400'],
        ]);

        $route = SavedRoute::findOrFail($validated['saved_route_id']);
        $user = $request->user();

        $eval = $this->engine->evaluate(
            (float) $route->latitude,
            (float) $route->longitude,
            $validated['activity_type'],
            (int) $validated['duration_minutes'],
            $user->pm25Threshold(),
            $route->name
        );

        $windowParts = explode(' - ', $eval['recommendation']['window_display']);
        $startStr = $windowParts[0] ?? '09:00';
        $endStr = $windowParts[1] ?? '10:00 AM';

        ClubBroadcast::create([
            'user_id' => $user->id,
            'club_name' => $validated['club_name'],
            'captain_name' => $user->name,
            'corridor_name' => $route->name,
            'latitude' => $route->latitude,
            'longitude' => $route->longitude,
            'activity_type' => $validated['activity_type'],
            'duration_minutes' => (int) $validated['duration_minutes'],
            'verdict' => $eval['status'],
            'window_start' => $startStr,
            'window_end' => $endStr,
            'session_date' => $validated['session_date'],
            'projected_pm2_5' => $eval['recommendation']['projected_pm2_5'],
            'improvement_pct' => $eval['recommendation']['improvement_pct'],
            'wind_summary' => $eval['current_conditions']['wind_speed_kmh'].' km/h '.$eval['current_conditions']['wind_dir'].' Coastal Flush',
            'captain_note' => $validated['captain_note'],
            'followers_count' => 1,
        ]);

        return redirect()->route('broadcasts.index')
            ->with('status', 'Club clearance broadcast published for '.$validated['club_name'].'.');
    }

    public function toggleFollow(Request $request, ClubBroadcast $broadcast): RedirectResponse
    {
        $user = $request->user();

        $exists = DB::table('club_broadcast_follows')
            ->where('user_id', $user->id)
            ->where('club_broadcast_id', $broadcast->id)
            ->exists();

        if ($exists) {
            DB::table('club_broadcast_follows')
                ->where('user_id', $user->id)
                ->where('club_broadcast_id', $broadcast->id)
                ->delete();
            $broadcast->decrement('followers_count');
            $msg = 'Unfollowed '.$broadcast->club_name.' broadcast.';
        } else {
            DB::table('club_broadcast_follows')->insert([
                'user_id' => $user->id,
                'club_broadcast_id' => $broadcast->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $broadcast->increment('followers_count');
            $msg = 'Following '.$broadcast->club_name.' clearance updates.';
        }

        return redirect()->route('broadcasts.index')->with('status', $msg);
    }

    public function destroy(Request $request, ClubBroadcast $broadcast): RedirectResponse
    {
        if ($broadcast->user_id !== $request->user()->id) {
            abort(403);
        }

        $broadcast->delete();

        return redirect()->route('broadcasts.index')
            ->with('status', 'Broadcast removed.');
    }
}
