<?php

namespace App\Http\Controllers;

use App\Models\SavedRoute;
use App\Services\ExposureEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavedRouteController extends Controller
{
    public function __construct(
        protected ExposureEngine $engine
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $threshold = $user ? $user->pm25Threshold() : 35.0;

        $routes = SavedRoute::query()
            ->where(function ($q) use ($user) {
                $q->where('is_preset', true);
                if ($user) {
                    $q->orWhere('user_id', $user->id);
                }
            })
            ->orderByDesc('is_preset')
            ->orderBy('name')
            ->get();

        $enrichedRoutes = $routes->map(function (SavedRoute $route) use ($threshold, $user) {
            $eval = $this->engine->evaluate(
                (float) $route->latitude,
                (float) $route->longitude,
                $route->preferred_activity,
                $user?->default_duration ?? 60,
                $threshold,
                $route->name
            );

            return [
                'model' => $route,
                'eval' => $eval,
            ];
        });

        return view('pages.routes', [
            'enrichedRoutes' => $enrichedRoutes,
            'unitSystem' => $user?->unit_system ?? 'metric',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'region' => ['required', 'string', 'max:120'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'distance_km' => ['required', 'numeric', 'min:0.5', 'max:250'],
            'preferred_activity' => ['required', 'string', 'in:running,cycling,walking'],
            'surface_type' => ['required', 'string', 'max:80'],
        ]);

        SavedRoute::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'region' => $validated['region'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'distance_km' => $validated['distance_km'],
            'preferred_activity' => $validated['preferred_activity'],
            'surface_type' => $validated['surface_type'],
            'is_preset' => false,
        ]);

        return redirect()->route('routes.index')
            ->with('status', 'Corridor "'.$validated['name'].'" saved to your hotspots.');
    }

    public function destroy(Request $request, SavedRoute $route): RedirectResponse
    {
        if ($route->is_preset || $route->user_id !== $request->user()->id) {
            abort(403, 'Preset corridors cannot be removed.');
        }

        $name = $route->name;
        $route->delete();

        return redirect()->route('routes.index')
            ->with('status', 'Corridor "'.$name.'" removed.');
    }
}
