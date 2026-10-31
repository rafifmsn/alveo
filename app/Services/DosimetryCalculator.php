<?php

namespace App\Services;

class DosimetryCalculator
{
    public const ZONES = [
        0 => [
            'zone' => 0,
            'name' => 'Resting Baseline',
            'short' => 'Resting',
            'ventilation_lpm' => 6.5,
            'deposition_fraction' => 0.38,
            'description' => 'Quiet nasal breathing at rest with high upper-airway filtration.',
        ],
        1 => [
            'zone' => 1,
            'name' => 'Zone 1 - Recovery Walk',
            'short' => 'Zone 1 Walk',
            'ventilation_lpm' => 18.0,
            'deposition_fraction' => 0.55,
            'description' => 'Light brisk walk or warm-up mobility; predominantly nasal airflow.',
        ],
        2 => [
            'zone' => 2,
            'name' => 'Zone 2 - Easy Aerobic Base',
            'short' => 'Zone 2 Base',
            'ventilation_lpm' => 44.0,
            'deposition_fraction' => 0.68,
            'description' => 'Conversational endurance run or steady spin; transition to oronasal breathing.',
        ],
        3 => [
            'zone' => 3,
            'name' => 'Zone 3 - Tempo / Marathon Effort',
            'short' => 'Zone 3 Tempo',
            'ventilation_lpm' => 66.0,
            'deposition_fraction' => 0.75,
            'description' => 'Sustained aerobic pace; open-mouth breathing bypasses nasal turbinate scrubbing.',
        ],
        4 => [
            'zone' => 4,
            'name' => 'Zone 4 - Lactate Threshold',
            'short' => 'Zone 4 Threshold',
            'ventilation_lpm' => 92.0,
            'deposition_fraction' => 0.82,
            'description' => 'High-velocity inspiratory flow drives fine particulates deep into bronchioles.',
        ],
        5 => [
            'zone' => 5,
            'name' => 'Zone 5 - VO2 Max Intervals',
            'short' => 'Zone 5 VO2 Max',
            'ventilation_lpm' => 118.0,
            'deposition_fraction' => 0.88,
            'description' => 'Peak pulmonary ventilation with maximal alveolar particulate retention.',
        ],
    ];

    public function defaultZoneForActivity(string $activityType): int
    {
        return match (strtolower($activityType)) {
            'walking', 'walk' => 1,
            'cycling', 'bike' => 2,
            default => 3,
        };
    }

    public function calculate(
        float $pm25,
        int $durationMinutes = 60,
        int $exertionZone = 3,
        float $o3 = 45.0,
        float $pm10 = 68.0
    ): array {
        $zoneConfig = self::ZONES[$exertionZone] ?? self::ZONES[3];
        $restingConfig = self::ZONES[0];

        $durationHours = max(5, $durationMinutes) / 60.0;
        $ventilationLpm = $zoneConfig['ventilation_lpm'];
        $depositionFraction = $zoneConfig['deposition_fraction'];

        $totalLiters = $ventilationLpm * $durationMinutes;
        $airVolumeM3 = $totalLiters / 1000.0;

        $restingAirVolumeM3 = ($restingConfig['ventilation_lpm'] * $durationMinutes) / 1000.0;
        $restingRetainedUg = $restingAirVolumeM3 * $pm25 * $restingConfig['deposition_fraction'];

        $rawInhaledPm25Ug = $airVolumeM3 * $pm25;
        $retainedPm25Ug = $rawInhaledPm25Ug * $depositionFraction;
        $retainedPm10Ug = $airVolumeM3 * $pm10 * ($depositionFraction * 0.65);
        $retainedO3Ug = $airVolumeM3 * $o3 * 0.60;

        $exertionMultiplier = $restingRetainedUg > 0
            ? round($retainedPm25Ug / $restingRetainedUg, 1)
            : round(($ventilationLpm * $depositionFraction) / ($restingConfig['ventilation_lpm'] * $restingConfig['deposition_fraction']), 1);

        $riskScore = $this->calculateRiskScore($pm25, $o3, $exertionZone, $durationMinutes);
        $usAqi = $this->pm25ToUsAqi($pm25);
        $epaIndex = $this->pm25ToEpaIndex($pm25);
        $defraIndex = $this->pm25ToDefraIndex($pm25);

        return [
            'zone' => $zoneConfig['zone'],
            'zone_name' => $zoneConfig['name'],
            'zone_short' => $zoneConfig['short'],
            'zone_description' => $zoneConfig['description'],
            'duration_minutes' => $durationMinutes,
            'duration_hours' => round($durationHours, 2),
            'ventilation_rate_lpm' => round($ventilationLpm, 1),
            'deposition_fraction' => $depositionFraction,
            'total_air_liters' => (int) round($totalLiters),
            'air_volume_m3' => round($airVolumeM3, 2),
            'ambient_pm2_5' => round($pm25, 1),
            'ambient_pm10' => round($pm10, 1),
            'ambient_o3' => round($o3, 1),
            'raw_inhaled_pm2_5_ug' => round($rawInhaledPm25Ug, 1),
            'inhaled_dose_ug' => round($retainedPm25Ug, 1),
            'retained_pm10_ug' => round($retainedPm10Ug, 1),
            'retained_o3_ug' => round($retainedO3Ug, 1),
            'resting_dose_ug' => round($restingRetainedUg, 1),
            'exertion_multiplier' => $exertionMultiplier,
            'exertion_risk_score' => $riskScore,
            'us_aqi' => $usAqi,
            'us_epa_index' => $epaIndex,
            'gb_defra_index' => $defraIndex,
            'defra_band' => $this->defraBandLabel($defraIndex),
            'risk_label' => $this->riskLabel($riskScore),
            'recovery_advisory' => $this->recoveryAdvisory($retainedPm25Ug, $riskScore),
        ];
    }

    public function calculateRiskScore(float $pm25, float $o3, int $zone = 3, int $durationMinutes = 60): float
    {
        $zoneFactor = match ($zone) {
            0 => 0.4,
            1 => 0.7,
            2 => 0.9,
            3 => 1.1,
            4 => 1.35,
            default => 1.55,
        };

        $durationFactor = min(1.5, max(0.6, $durationMinutes / 60.0));
        $baseScore = (($pm25 / 10.5) + ($o3 / 65.0)) * $zoneFactor * sqrt($durationFactor);

        return round(min(10.0, max(1.0, $baseScore)), 1);
    }

    public function pm25ToUsAqi(float $pm25): int
    {
        $c = max(0.0, $pm25);

        $breakpoints = [
            [0.0, 12.0, 0, 50],
            [12.1, 35.4, 51, 100],
            [35.5, 55.4, 101, 150],
            [55.5, 150.4, 151, 200],
            [150.5, 250.4, 201, 300],
            [250.5, 500.4, 301, 500],
        ];

        foreach ($breakpoints as [$cLow, $cHigh, $iLow, $iHigh]) {
            if ($c <= $cHigh) {
                return (int) round((($iHigh - $iLow) / ($cHigh - $cLow)) * ($c - $cLow) + $iLow);
            }
        }

        return 500;
    }

    public function pm25ToEpaIndex(float $pm25): int
    {
        return match (true) {
            $pm25 <= 12.0 => 1,
            $pm25 <= 35.4 => 2,
            $pm25 <= 55.4 => 3,
            $pm25 <= 150.4 => 4,
            $pm25 <= 250.4 => 5,
            default => 6,
        };
    }

    public function pm25ToDefraIndex(float $pm25): int
    {
        return match (true) {
            $pm25 <= 11.0 => 1,
            $pm25 <= 23.0 => 2,
            $pm25 <= 35.0 => 3,
            $pm25 <= 41.0 => 4,
            $pm25 <= 47.0 => 5,
            $pm25 <= 53.0 => 6,
            $pm25 <= 58.0 => 7,
            $pm25 <= 64.0 => 8,
            $pm25 <= 70.0 => 9,
            default => 10,
        };
    }

    public function defraBandLabel(int $defraIndex): string
    {
        return match (true) {
            $defraIndex <= 3 => 'Low',
            $defraIndex <= 6 => 'Moderate',
            $defraIndex <= 9 => 'High',
            default => 'Very High',
        };
    }

    public function riskLabel(float $riskScore): string
    {
        return match (true) {
            $riskScore <= 3.5 => 'Optimal Clearance',
            $riskScore <= 5.5 => 'Moderate Exertion Load',
            $riskScore <= 7.5 => 'Elevated Pulmonary Load',
            default => 'High Inhaled Exposure',
        };
    }

    public function recoveryAdvisory(float $retainedUg, float $riskScore): string
    {
        if ($riskScore <= 3.5) {
            return 'Particulate deposition is within safe athletic tolerances. Standard post-session electrolyte hydration is sufficient.';
        }

        if ($riskScore <= 6.5) {
            return 'Moderate fine-particulate retention detected. Perform saline nasal irrigation post-workout and prioritize Vitamin C and Omega-3 intake to buffer airway oxidative stress.';
        }

        return 'Elevated alveolar PM2.5 deposition ('.round($retainedUg, 1).' ug). Avoid high-intensity intervals for the next 12 hours, use isotonic nasal rinse immediately, and increase antioxidant-rich hydration.';
    }
}
