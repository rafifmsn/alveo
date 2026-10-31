<?php

namespace App\Http\Controllers;

use App\Models\SavedRoute;
use App\Models\WorkoutLog;
use App\Services\DosimetryCalculator;
use App\Services\WeatherService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExposureHistoryController extends Controller
{
    public function __construct(
        protected DosimetryCalculator $dosimetry,
        protected WeatherService $weather
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

        $logs = WorkoutLog::query()
            ->where('user_id', $user->id)
            ->orderByDesc('trained_at')
            ->get();

        $weekLogs = $logs->filter(fn (WorkoutLog $w) => $w->trained_at >= now()->subDays(7));

        $weeklyDoseUg = round((float) $weekLogs->sum('inhaled_dose_ug'), 1);
        $weeklyMinutes = (int) $weekLogs->sum('duration_minutes');
        $weeklyAvgPm25 = $weekLogs->isNotEmpty()
            ? round((float) $weekLogs->avg('ambient_pm2_5'), 1)
            : 0.0;
        $weeklyAirVolumeM3 = round((float) $weekLogs->sum('air_volume_m3'), 1);

        // WHO 24h mean reference is 15 ug/m3; compute weekly reference ceiling for active hours
        $whoCeilingUg = round(max(120.0, $weeklyAirVolumeM3 * 15.0 * 0.75), 1);

        return view('pages.history', [
            'logs' => $logs,
            'corridors' => $corridors,
            'weeklyStats' => [
                'dose_ug' => $weeklyDoseUg,
                'minutes' => $weeklyMinutes,
                'sessions_count' => $weekLogs->count(),
                'avg_pm2_5' => $weeklyAvgPm25,
                'air_volume_m3' => $weeklyAirVolumeM3,
                'who_ceiling_ug' => $whoCeilingUg,
                'ceiling_pct' => min(100, (int) round(($weeklyDoseUg / max(1.0, $whoCeilingUg)) * 100)),
            ],
            'zonesConfig' => DosimetryCalculator::ZONES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $minDate = Carbon::today('Asia/Jakarta')->subDays(2)->toDateString();
        $maxDate = Carbon::today('Asia/Jakarta')->addDays(2)->toDateString();

        $validated = $request->validate([
            'saved_route_id' => ['nullable', 'integer', 'exists:saved_routes,id'],
            'custom_location' => ['nullable', 'string', 'max:140'],
            'activity_type' => ['required', 'string', 'in:running,cycling,walking'],
            'exertion_zone' => ['required', 'integer', 'between:1,5'],
            'duration_minutes' => ['required', 'integer', 'between:10,360'],
            'ambient_pm2_5' => ['nullable', 'numeric', 'between:1,500'],
            'trained_date' => ['required', 'date', "after_or_equal:{$minDate}", "before_or_equal:{$maxDate}"],
            'trained_time' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:300'],
        ], [
            'trained_date.after_or_equal' => 'Session date must be within the active 3-day forecast window.',
            'trained_date.before_or_equal' => 'Session date cannot exceed the 3-day forecast horizon.',
        ]);

        $route = null;
        if (! empty($validated['saved_route_id'])) {
            $route = SavedRoute::find($validated['saved_route_id']);
        }

        $locationName = $route?->name ?: ($validated['custom_location'] ?: 'GBK Outer Perimeter, Jakarta');

        $pm25 = isset($validated['ambient_pm2_5']) && $validated['ambient_pm2_5'] !== null
            ? (float) $validated['ambient_pm2_5']
            : null;
        $o3 = 46.0;

        if ($pm25 === null && $route) {
            $forecast = $this->weather->getForecast((float) $route->latitude, (float) $route->longitude, $route->name);
            $pm25 = (float) $forecast['current']['pm2_5'];
            $o3 = (float) $forecast['current']['o3'];
        }

        $pm25 = $pm25 ?? 26.5;

        $calc = $this->dosimetry->calculate(
            $pm25,
            (int) $validated['duration_minutes'],
            (int) $validated['exertion_zone'],
            $o3
        );

        $trainedAt = Carbon::parse($validated['trained_date'].' '.$validated['trained_time'], 'Asia/Jakarta');

        WorkoutLog::create([
            'user_id' => $request->user()->id,
            'saved_route_id' => $route?->id,
            'location_name' => $locationName,
            'activity_type' => $validated['activity_type'],
            'exertion_zone' => (int) $validated['exertion_zone'],
            'duration_minutes' => (int) $validated['duration_minutes'],
            'ambient_pm2_5' => $calc['ambient_pm2_5'],
            'ambient_o3' => $calc['ambient_o3'],
            'ventilation_rate_lpm' => $calc['ventilation_rate_lpm'],
            'air_volume_m3' => $calc['air_volume_m3'],
            'inhaled_dose_ug' => $calc['inhaled_dose_ug'],
            'exertion_risk_score' => $calc['exertion_risk_score'],
            'trained_at' => $trainedAt,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('history.index')
            ->with('status', 'Training session logged ('.$calc['inhaled_dose_ug'].' ug retained PM2.5).');
    }

    public function destroy(Request $request, WorkoutLog $workout): RedirectResponse
    {
        if ($workout->user_id !== $request->user()->id) {
            abort(403);
        }

        $workout->delete();

        return redirect()->route('history.index')
            ->with('status', 'Workout log entry removed.');
    }
}
