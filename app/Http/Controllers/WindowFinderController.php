<?php

namespace App\Http\Controllers;

use App\Models\SavedRoute;
use App\Services\ExposureEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class WindowFinderController extends Controller
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

        $selectedRoute = null;
        if ($request->filled('route_id')) {
            $selectedRoute = $corridors->firstWhere('id', (int) $request->input('route_id'));
        }

        $lat = (float) ($selectedRoute?->latitude ?? $request->input('lat', -6.2185));
        $lon = (float) ($selectedRoute?->longitude ?? $request->input('lon', 106.8025));
        $locationLabel = $selectedRoute?->name ?? $request->input('location', 'GBK Outer Perimeter, Jakarta');

        if ($request->filled('location') && ! $request->filled('lat') && ! $selectedRoute) {
            $matched = $corridors->first(function (SavedRoute $r) use ($locationLabel) {
                return stripos($r->name, (string) $locationLabel) !== false
                    || stripos($r->region, (string) $locationLabel) !== false;
            });

            if ($matched) {
                $lat = (float) $matched->latitude;
                $lon = (float) $matched->longitude;
                $locationLabel = $matched->name;
                $selectedRoute = $matched;
            }
        }

        $activity = strtolower((string) $request->input('activity', $selectedRoute?->preferred_activity ?? $user?->default_activity ?? 'running'));
        if (! in_array($activity, ['running', 'cycling', 'walking'], true)) {
            $activity = 'running';
        }

        $duration = (int) $request->input('duration', $user?->default_duration ?? 60);
        if (! in_array($duration, [30, 60, 90, 120], true)) {
            $duration = 60;
        }

        $threshold = $user ? $user->pm25Threshold() : 35.0;

        $evaluation = $this->engine->evaluate(
            $lat,
            $lon,
            $activity,
            $duration,
            $threshold,
            (string) $locationLabel
        );

        return view('pages.finder', [
            'evaluation' => $evaluation,
            'corridors' => $corridors,
            'selectedRoute' => $selectedRoute,
            'unitSystem' => $user?->unit_system ?? 'metric',
        ]);
    }

    public function downloadIcs(Request $request): Response
    {
        $lat = (float) $request->input('lat', -6.2185);
        $lon = (float) $request->input('lon', 106.8025);
        $activity = (string) $request->input('activity', 'running');
        $duration = (int) $request->input('duration', 60);
        $location = (string) $request->input('location', 'GBK Outer Perimeter, Jakarta');
        $threshold = $request->user() ? $request->user()->pm25Threshold() : 35.0;

        $evaluation = $this->engine->evaluate($lat, $lon, $activity, $duration, $threshold, $location);
        $ics = $evaluation['actions']['ics_download_payload'];

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="alveo-clean-window.ics"',
        ]);
    }

    public function evaluateApi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'activity_type' => ['required', 'string', 'in:running,cycling,walking'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:240'],
            'include_ai_brief' => ['nullable', 'boolean'],
            'location_name' => ['nullable', 'string', 'max:140'],
        ]);

        $duration = (int) ($validated['duration_minutes'] ?? 60);
        $includeAi = (bool) ($validated['include_ai_brief'] ?? true);

        $evaluation = $this->engine->evaluate(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            (string) $validated['activity_type'],
            $duration,
            35.0,
            $validated['location_name'] ?? null
        );

        return response()->json([
            'status' => $evaluation['status'],
            'current_conditions' => [
                'timestamp' => $evaluation['current_conditions']['timestamp'],
                'pm2_5' => $evaluation['current_conditions']['pm2_5'],
                'us_aqi' => $evaluation['current_conditions']['us_aqi'],
                'wind_speed_kmh' => $evaluation['current_conditions']['wind_speed_kmh'],
                'exertion_risk_score' => $evaluation['current_conditions']['exertion_risk_score'],
            ],
            'recommendation' => [
                'best_start_time' => $evaluation['recommendation']['best_start_time'],
                'best_end_time' => $evaluation['recommendation']['best_end_time'],
                'projected_pm2_5' => $evaluation['recommendation']['projected_pm2_5'],
                'improvement_pct' => $evaluation['recommendation']['improvement_pct'],
            ],
            'ai_briefing' => $includeAi ? $evaluation['ai_briefing'] : null,
            'actions' => [
                'google_calendar_url' => $evaluation['actions']['google_calendar_url'],
                'm365_calendar_url' => $evaluation['actions']['m365_calendar_url'],
                'outlook_calendar_url' => $evaluation['actions']['outlook_calendar_url'],
                'yahoo_calendar_url' => $evaluation['actions']['yahoo_calendar_url'],
                'telegram_share_url' => $evaluation['actions']['telegram_share_url'],
                'mailto_share_url' => $evaluation['actions']['mailto_share_url'],
                'ics_download_payload' => $evaluation['actions']['ics_download_payload'],
            ],
        ]);
    }
}
