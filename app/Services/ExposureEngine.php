<?php

namespace App\Services;

use Carbon\Carbon;

class ExposureEngine
{
    public function __construct(
        protected WeatherService $weather,
        protected DosimetryCalculator $dosimetry
    ) {}

    public function evaluate(
        float $latitude,
        float $longitude,
        string $activityType = 'running',
        int $durationMinutes = 60,
        float $pm25Threshold = 35.0,
        ?string $locationLabel = null
    ): array {
        $forecast = $this->weather->getForecast($latitude, $longitude, $locationLabel);
        $zone = $this->dosimetry->defaultZoneForActivity($activityType);
        $locationName = $forecast['location']['name'];

        $current = $forecast['current'];
        $currentDosimetry = $this->dosimetry->calculate(
            (float) $current['pm2_5'],
            $durationMinutes,
            $zone,
            (float) $current['o3'],
            (float) $current['pm10']
        );

        $slotsNeeded = max(1, (int) ceil($durationMinutes / 60));
        $timelineDays = [];
        $todayOptimal = null;

        foreach ($forecast['days'] as $dayIdx => $day) {
            $trainingHours = array_values(array_filter(
                $day['hours'],
                fn (array $h) => $h['hour'] >= 6 && $h['hour'] <= 18
            ));

            $bestIndex = 0;
            $bestScore = PHP_FLOAT_MAX;

            for ($i = 0; $i <= max(0, count($trainingHours) - $slotsNeeded); $i++) {
                $slice = array_slice($trainingHours, $i, $slotsNeeded);
                $avgPm25 = array_sum(array_column($slice, 'pm2_5')) / count($slice);
                $avgO3 = array_sum(array_column($slice, 'o3')) / count($slice);
                $avgUv = array_sum(array_column($slice, 'uv')) / count($slice);

                $penaltyScore = $avgPm25 + ($avgO3 * 0.12) + ($avgUv * 0.65);
                if ($penaltyScore < $bestScore) {
                    $bestScore = $penaltyScore;
                    $bestIndex = $i;
                }
            }

            $optimalHour = $trainingHours[$bestIndex] ?? $day['hours'][9];
            $startHour = (int) $optimalHour['hour'];
            $startCarbon = Carbon::parse($day['date'].sprintf(' %02d:00:00', $startHour), 'Asia/Jakarta');
            $endCarbon = $startCarbon->copy()->addMinutes($durationMinutes);

            $windowSlots = [];
            foreach (array_slice($trainingHours, 0, 6) as $idx => $slot) {
                $isOptimal = ($slot['hour'] === $startHour);
                $windowSlots[] = [
                    'hour' => $slot['hour'],
                    'time_range' => sprintf(
                        '%02d:00 - %02d:00 %s',
                        $slot['hour'],
                        ($slot['hour'] + 1) % 24,
                        ($slot['hour'] + 1) < 12 ? 'AM' : 'PM'
                    ),
                    'pm2_5' => $slot['pm2_5'],
                    'wind_kph' => $slot['wind_kph'],
                    'wind_dir' => $slot['wind_dir'],
                    'uv' => $slot['uv'],
                    'regime_tag' => $slot['regime_tag'],
                    'is_optimal' => $isOptimal,
                    'severity' => $this->slotSeverity($slot['pm2_5'], $isOptimal),
                ];
            }

            $daySummary = [
                'date' => $day['date'],
                'day_label' => $day['day_label'],
                'best_start_iso' => $startCarbon->toIso8601String(),
                'best_end_iso' => $endCarbon->toIso8601String(),
                'window_display' => $startCarbon->format('h:i').' - '.$endCarbon->format('h:i A'),
                'short_window' => $startCarbon->format('H:i').' - '.$endCarbon->format('H:i'),
                'projected_pm2_5' => $optimalHour['pm2_5'],
                'wind_kph' => $optimalHour['wind_kph'],
                'wind_dir' => $optimalHour['wind_dir'],
                'slots' => $windowSlots,
            ];

            if ($dayIdx === 0) {
                $todayOptimal = $daySummary;
            }

            $timelineDays[] = $daySummary;
        }

        $projectedPm25 = (float) ($todayOptimal['projected_pm2_5'] ?? 22.1);
        $currentPm25 = (float) $current['pm2_5'];

        $improvementPct = $currentPm25 > 0
            ? round(max(0.0, (($currentPm25 - $projectedPm25) / $currentPm25) * 100), 2)
            : 0.0;

        $optimalDosimetry = $this->dosimetry->calculate(
            $projectedPm25,
            $durationMinutes,
            $zone,
            (float) $current['o3'],
            round($projectedPm25 * 1.4, 1)
        );

        $status = ($currentPm25 <= $pm25Threshold && $improvementPct < 20.0) ? 'GO' : 'DELAY';

        $briefing = $this->buildMeteorologicalBriefing(
            $status,
            $currentPm25,
            $projectedPm25,
            $improvementPct,
            (string) $current['wind_dir'],
            (float) $todayOptimal['wind_kph'],
            (string) $todayOptimal['window_display'],
            $locationName
        );

        $icsPayload = $this->generateIcsPayload(
            $locationName,
            $activityType,
            $todayOptimal['best_start_iso'],
            $todayOptimal['best_end_iso'],
            $projectedPm25,
            $improvementPct,
            $briefing
        );

        $googleCalUrl = $this->generateGoogleCalendarUrl(
            $locationName,
            $activityType,
            $todayOptimal['best_start_iso'],
            $todayOptimal['best_end_iso'],
            $projectedPm25,
            $improvementPct,
            $briefing
        );

        $telegramCardText = sprintf(
            "[ALVEO CLEARANCE: %s]\nCorridor: %s\nActivity: %s (%dm)\nOptimal Window: %s\nProjected PM2.5: %.1f ug/m3 (-%.0f%% inhaled load)\nSurface Wind: %.1f km/h %s\nAnalysis: %s",
            $status === 'GO' ? 'GO - CLEAR' : 'RESCHEDULE SESSION',
            $locationName,
            ucfirst($activityType),
            $durationMinutes,
            $todayOptimal['window_display'],
            $projectedPm25,
            $improvementPct,
            $todayOptimal['wind_kph'],
            $todayOptimal['wind_dir'],
            $briefing
        );

        $telegramShareUrl = 'https://t.me/share/url?'.http_build_query([
            'url' => config('app.url'),
            'text' => $telegramCardText,
        ]);

        return [
            'status' => $status,
            'source' => $forecast['source'],
            'cached_at' => $forecast['cached_at'],
            'location' => $forecast['location'],
            'activity_type' => $activityType,
            'duration_minutes' => $durationMinutes,
            'pm25_threshold' => $pm25Threshold,
            'current_conditions' => [
                'timestamp' => $current['timestamp'],
                'pm2_5' => $currentPm25,
                'pm10' => $current['pm10'],
                'o3' => $current['o3'],
                'no2' => $current['no2'],
                'so2' => $current['so2'],
                'co' => $current['co'],
                'us_aqi' => $current['us_aqi'],
                'us_epa_index' => $current['us_epa_index'],
                'gb_defra_index' => $current['gb_defra_index'],
                'wind_speed_kmh' => $current['wind_kph'],
                'wind_speed_mph' => $current['wind_mph'],
                'wind_dir' => $current['wind_dir'],
                'temp_c' => $current['temp_c'],
                'temp_f' => $current['temp_f'],
                'humidity' => $current['humidity'],
                'uv' => $current['uv'],
                'exertion_risk_score' => $currentDosimetry['exertion_risk_score'],
                'inhaled_dose_ug' => $currentDosimetry['inhaled_dose_ug'],
                'exertion_multiplier' => $currentDosimetry['exertion_multiplier'],
            ],
            'recommendation' => [
                'best_start_time' => $todayOptimal['best_start_iso'],
                'best_end_time' => $todayOptimal['best_end_iso'],
                'window_display' => $todayOptimal['window_display'],
                'short_window' => $todayOptimal['short_window'],
                'delta_label' => $status === 'DELAY' ? 'DELTA: +3H 00M' : 'READY NOW',
                'projected_pm2_5' => $projectedPm25,
                'projected_dose_ug' => $optimalDosimetry['inhaled_dose_ug'],
                'improvement_pct' => $improvementPct,
            ],
            'ai_briefing' => $briefing,
            'timeline_days' => $timelineDays,
            'actions' => [
                'google_calendar_url' => $googleCalUrl,
                'telegram_share_url' => $telegramShareUrl,
                'telegram_card_text' => $telegramCardText,
                'ics_download_payload' => $icsPayload,
            ],
        ];
    }

    protected function slotSeverity(float $pm25, bool $isOptimal): string
    {
        if ($isOptimal) {
            return 'optimal';
        }
        if ($pm25 >= 55.0) {
            return 'high';
        }
        if ($pm25 >= 45.0) {
            return 'commuter';
        }
        if ($pm25 >= 32.0) {
            return 'moderate';
        }

        return 'low';
    }

    protected function buildMeteorologicalBriefing(
        string $status,
        float $currentPm25,
        float $projectedPm25,
        float $improvementPct,
        string $windDir,
        float $optimalWindKph,
        string $windowDisplay,
        string $locationName
    ): string {
        if ($status === 'GO') {
            return sprintf(
                'Active %s surface ventilation (%.1f km/h) is maintaining clean dispersion across %s at %.1f ug/m3. Conditions are clear for immediate aerobic training.',
                $windDir,
                $optimalWindKph,
                $locationName,
                $currentPm25
            );
        }

        return sprintf(
            'Surface temperature inversion is trapping vehicle exhaust along primary arteries (%.1f ug/m3). Rising %s sea breeze (%.1f km/h) clears the corridor by %s, cutting inhaled PM2.5 dose by %.0f%%.',
            $currentPm25,
            $windDir,
            $optimalWindKph,
            $windowDisplay,
            $improvementPct
        );
    }

    protected function generateGoogleCalendarUrl(
        string $locationName,
        string $activityType,
        string $startIso,
        string $endIso,
        float $projectedPm25,
        float $improvementPct,
        string $briefing
    ): string {
        $startUtc = Carbon::parse($startIso)->utc()->format('Ymd\THis\Z');
        $endUtc = Carbon::parse($endIso)->utc()->format('Ymd\THis\Z');

        $title = sprintf('Alveo Clean-Air %s Session (%s)', ucfirst($activityType), $locationName);
        $details = sprintf(
            "Optimal Aerobic Window verified by Alveo.\nProjected PM2.5: %.1f ug/m3 (-%.0f%% inhaled exposure)\n\nMicro-Meteorological Analysis:\n%s",
            $projectedPm25,
            $improvementPct,
            $briefing
        );

        return 'https://calendar.google.com/calendar/render?'.http_build_query([
            'action' => 'TEMPLATE',
            'text' => $title,
            'dates' => "{$startUtc}/{$endUtc}",
            'location' => $locationName,
            'details' => $details,
        ]);
    }

    public function generateIcsPayload(
        string $locationName,
        string $activityType,
        string $startIso,
        string $endIso,
        float $projectedPm25,
        float $improvementPct,
        string $briefing
    ): string {
        $startUtc = Carbon::parse($startIso)->utc()->format('Ymd\THis\Z');
        $endUtc = Carbon::parse($endIso)->utc()->format('Ymd\THis\Z');
        $nowUtc = now()->utc()->format('Ymd\THis\Z');
        $uid = md5($locationName.$startIso.$activityType).'@alveo.id';
        $summary = sprintf('Alveo Clean-Air %s (%s)', ucfirst($activityType), $locationName);
        $desc = sprintf(
            'Projected PM2.5: %.1f ug/m3 (-%.0f%% exposure). %s',
            $projectedPm25,
            $improvementPct,
            $briefing
        );

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Alveo Exposure Engine//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            "UID:{$uid}",
            "DTSTAMP:{$nowUtc}",
            "DTSTART:{$startUtc}",
            "DTEND:{$endUtc}",
            "SUMMARY:{$summary}",
            "LOCATION:{$locationName}",
            "DESCRIPTION:{$desc}",
            'BEGIN:VALARM',
            'TRIGGER:-PT15M',
            'ACTION:DISPLAY',
            'DESCRIPTION:Clean-air training window opens in 15 minutes',
            'END:VALARM',
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", $lines);
    }
}
