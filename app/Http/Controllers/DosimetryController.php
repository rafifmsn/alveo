<?php

namespace App\Http\Controllers;

use App\Models\SavedRoute;
use App\Services\DosimetryCalculator;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DosimetryController extends Controller
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

        $pm25 = (float) $request->input('pm2_5', 58.4);
        $o3 = (float) $request->input('o3', 48.0);
        $pm10 = (float) $request->input('pm10', round($pm25 * 1.42, 1));
        $zone = (int) $request->input('zone', 3);
        $duration = (int) $request->input('duration', $user?->default_duration ?? 60);

        if ($request->filled('route_id')) {
            $route = $corridors->firstWhere('id', (int) $request->input('route_id'));
            if ($route) {
                $forecast = $this->weather->getForecast((float) $route->latitude, (float) $route->longitude, $route->name);
                $pm25 = (float) $forecast['current']['pm2_5'];
                $o3 = (float) $forecast['current']['o3'];
                $pm10 = (float) $forecast['current']['pm10'];
            }
        }

        $activeResult = $this->dosimetry->calculate($pm25, $duration, $zone, $o3, $pm10);
        $cleanWindowResult = $this->dosimetry->calculate(max(12.0, round($pm25 * 0.38, 1)), $duration, $zone, $o3 * 0.85, $pm10 * 0.4);

        $zoneComparison = [];
        foreach (DosimetryCalculator::ZONES as $zNum => $zData) {
            $zoneComparison[$zNum] = $this->dosimetry->calculate($pm25, $duration, $zNum, $o3, $pm10);
        }

        return view('pages.dosimetry', [
            'activeResult' => $activeResult,
            'cleanWindowResult' => $cleanWindowResult,
            'zoneComparison' => $zoneComparison,
            'zonesConfig' => DosimetryCalculator::ZONES,
            'corridors' => $corridors,
        ]);
    }
}
