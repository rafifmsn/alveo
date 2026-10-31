<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class WeatherService
{
    public function __construct(
        protected DosimetryCalculator $dosimetry
    ) {}

    public function normalizeCoord(float $coord): float
    {
        return round($coord, 2);
    }

    public function cacheKey(float $latitude, float $longitude): string
    {
        $lat = number_format($this->normalizeCoord($latitude), 2, '.', '');
        $lon = number_format($this->normalizeCoord($longitude), 2, '.', '');

        return "weatherapi_forecast_{$lat}_{$lon}";
    }

    public function getForecast(float $latitude, float $longitude, ?string $locationLabel = null): array
    {
        $key = $this->cacheKey($latitude, $longitude);
        $ttl = (int) config('services.weatherapi.cache_ttl', 2700);

        return Cache::remember($key, $ttl, function () use ($latitude, $longitude, $locationLabel) {
            $apiKey = (string) config('services.weatherapi.key', '');
            $baseUrl = rtrim((string) config('services.weatherapi.base_url', 'https://api.weatherapi.com/v1'), '/');

            if ($apiKey !== '') {
                try {
                    $response = Http::timeout(6)->get("{$baseUrl}/forecast.json", [
                        'key' => $apiKey,
                        'q' => "{$latitude},{$longitude}",
                        'days' => 3,
                        'aqi' => 'yes',
                        'alerts' => 'no',
                    ]);

                    if ($response->successful() && is_array($response->json('forecast.forecastday'))) {
                        return $this->normalizeWeatherApiPayload($response->json(), $latitude, $longitude, $locationLabel);
                    }
                } catch (Throwable) {
                    // Fall through to deterministic micro-meteorological model
                }
            }

            return $this->buildDeterministicForecast($latitude, $longitude, $locationLabel);
        });
    }

    protected function normalizeWeatherApiPayload(array $raw, float $lat, float $lon, ?string $locationLabel = null): array
    {
        $loc = $raw['location'] ?? [];
        $cur = $raw['current'] ?? [];
        $curAqi = $cur['air_quality'] ?? [];

        $pm25 = (float) ($curAqi['pm2_5'] ?? 38.0);
        $pm10 = (float) ($curAqi['pm10'] ?? ($pm25 * 1.45));
        $o3 = (float) ($curAqi['o3'] ?? 48.0);
        $no2 = (float) ($curAqi['no2'] ?? 24.0);
        $so2 = (float) ($curAqi['so2'] ?? 8.5);
        $co = (float) ($curAqi['co'] ?? 420.0);

        $days = [];
        foreach (($raw['forecast']['forecastday'] ?? []) as $dayIndex => $dayData) {
            $hours = [];
            foreach (($dayData['hour'] ?? []) as $h) {
                $hAqi = $h['air_quality'] ?? [];
                $hPm25 = (float) ($hAqi['pm2_5'] ?? $pm25);
                $hPm10 = (float) ($hAqi['pm10'] ?? ($hPm25 * 1.4));
                $hO3 = (float) ($hAqi['o3'] ?? $o3);
                $windKph = (float) ($h['wind_kph'] ?? 8.0);
                $windDir = (string) ($h['wind_dir'] ?? 'NW');
                $uv = (float) ($h['uv'] ?? 3.0);
                $tempC = (float) ($h['temp_c'] ?? 28.0);
                $humidity = (int) ($h['humidity'] ?? 70);
                $timeStr = (string) ($h['time'] ?? now()->format('Y-m-d H:00'));
                $hourNum = (int) Carbon::parse($timeStr)->format('G');

                $hours[] = [
                    'time' => $timeStr,
                    'hour' => $hourNum,
                    'label' => sprintf('%02d:00 - %02d:00 %s', $hourNum, ($hourNum + 1) % 24, $hourNum < 12 ? 'AM' : 'PM'),
                    'temp_c' => round($tempC, 1),
                    'temp_f' => round(($tempC * 9 / 5) + 32, 1),
                    'humidity' => $humidity,
                    'wind_kph' => round($windKph, 1),
                    'wind_mph' => round($windKph * 0.621371, 1),
                    'wind_dir' => $windDir,
                    'wind_degree' => (int) ($h['wind_degree'] ?? 315),
                    'uv' => round($uv, 1),
                    'condition_text' => (string) ($h['condition']['text'] ?? 'Partly Cloudy'),
                    'pm2_5' => round($hPm25, 1),
                    'pm10' => round($hPm10, 1),
                    'o3' => round($hO3, 1),
                    'no2' => round((float) ($hAqi['no2'] ?? $no2), 1),
                    'so2' => round((float) ($hAqi['so2'] ?? $so2), 1),
                    'co' => round((float) ($hAqi['co'] ?? $co), 1),
                    'us_epa_index' => (int) ($hAqi['us-epa-index'] ?? $this->dosimetry->pm25ToEpaIndex($hPm25)),
                    'gb_defra_index' => (int) ($hAqi['gb-defra-index'] ?? $this->dosimetry->pm25ToDefraIndex($hPm25)),
                    'us_aqi' => $this->dosimetry->pm25ToUsAqi($hPm25),
                    'regime_tag' => $this->describeAtmosphericRegime($hourNum, $hPm25, $windKph, $uv),
                ];
            }

            $days[] = [
                'date' => (string) ($dayData['date'] ?? now()->addDays($dayIndex)->toDateString()),
                'day_label' => $dayIndex === 0 ? 'Today' : ($dayIndex === 1 ? 'Tomorrow' : Carbon::parse($dayData['date'] ?? now()->addDays($dayIndex))->format('l')),
                'hours' => $hours,
            ];
        }

        return [
            'source' => 'weatherapi_live',
            'cached_at' => now()->toIso8601String(),
            'location' => [
                'name' => $locationLabel ?: (($loc['name'] ?? 'Jakarta').(isset($loc['region']) && $loc['region'] !== '' ? ', '.$loc['region'] : '')),
                'latitude' => $lat,
                'longitude' => $lon,
                'tz_id' => $loc['tz_id'] ?? 'Asia/Jakarta',
            ],
            'current' => [
                'timestamp' => now()->toIso8601String(),
                'temp_c' => round((float) ($cur['temp_c'] ?? 27.5), 1),
                'temp_f' => round((float) ($cur['temp_f'] ?? 81.5), 1),
                'humidity' => (int) ($cur['humidity'] ?? 74),
                'wind_kph' => round((float) ($cur['wind_kph'] ?? 4.2), 1),
                'wind_mph' => round((float) ($cur['wind_mph'] ?? 2.6), 1),
                'wind_dir' => (string) ($cur['wind_dir'] ?? 'NW'),
                'wind_degree' => (int) ($cur['wind_degree'] ?? 310),
                'uv' => round((float) ($cur['uv'] ?? 2.0), 1),
                'pm2_5' => round($pm25, 1),
                'pm10' => round($pm10, 1),
                'o3' => round($o3, 1),
                'no2' => round($no2, 1),
                'so2' => round($so2, 1),
                'co' => round($co, 1),
                'us_epa_index' => (int) ($curAqi['us-epa-index'] ?? $this->dosimetry->pm25ToEpaIndex($pm25)),
                'gb_defra_index' => (int) ($curAqi['gb-defra-index'] ?? $this->dosimetry->pm25ToDefraIndex($pm25)),
                'us_aqi' => $this->dosimetry->pm25ToUsAqi($pm25),
            ],
            'days' => $days,
        ];
    }

    public function buildDeterministicForecast(float $lat, float $lon, ?string $locationLabel = null): array
    {
        $coordSeed = (int) round(abs($lat * 100) + abs($lon * 100));
        $coastalFactor = ($lat > -6.16) ? -6.5 : (($lat < -6.32) ? -4.0 : 0.0);
        $corridorOffset = (($coordSeed % 9) - 4) * 1.2 + $coastalFactor;

        $hourlyProfile = [
            0 => ['pm' => 44.0, 'wind' => 3.5, 'dir' => 'S', 'temp' => 25.4, 'uv' => 0.0, 'o3' => 28.0],
            1 => ['pm' => 46.2, 'wind' => 3.2, 'dir' => 'S', 'temp' => 25.1, 'uv' => 0.0, 'o3' => 26.0],
            2 => ['pm' => 48.5, 'wind' => 3.0, 'dir' => 'SSW', 'temp' => 24.8, 'uv' => 0.0, 'o3' => 25.0],
            3 => ['pm' => 51.0, 'wind' => 2.8, 'dir' => 'SSW', 'temp' => 24.6, 'uv' => 0.0, 'o3' => 24.0],
            4 => ['pm' => 54.2, 'wind' => 3.1, 'dir' => 'SW', 'temp' => 24.5, 'uv' => 0.0, 'o3' => 26.0],
            5 => ['pm' => 56.8, 'wind' => 3.6, 'dir' => 'SW', 'temp' => 24.8, 'uv' => 0.5, 'o3' => 30.0],
            6 => ['pm' => 58.4, 'wind' => 4.2, 'dir' => 'NW', 'temp' => 25.6, 'uv' => 1.8, 'o3' => 36.0],
            7 => ['pm' => 52.0, 'wind' => 6.4, 'dir' => 'NW', 'temp' => 26.9, 'uv' => 3.5, 'o3' => 42.0],
            8 => ['pm' => 38.0, 'wind' => 9.2, 'dir' => 'NW', 'temp' => 28.3, 'uv' => 5.4, 'o3' => 48.0],
            9 => ['pm' => 22.1, 'wind' => 12.8, 'dir' => 'NNW', 'temp' => 29.4, 'uv' => 6.8, 'o3' => 52.0],
            10 => ['pm' => 24.0, 'wind' => 14.1, 'dir' => 'NNW', 'temp' => 30.5, 'uv' => 8.2, 'o3' => 58.0],
            11 => ['pm' => 26.5, 'wind' => 15.0, 'dir' => 'N', 'temp' => 31.4, 'uv' => 9.5, 'o3' => 66.0],
            12 => ['pm' => 28.2, 'wind' => 15.6, 'dir' => 'N', 'temp' => 32.0, 'uv' => 10.1, 'o3' => 74.0],
            13 => ['pm' => 29.8, 'wind' => 14.8, 'dir' => 'NNE', 'temp' => 32.2, 'uv' => 9.4, 'o3' => 78.0],
            14 => ['pm' => 27.4, 'wind' => 13.9, 'dir' => 'NNE', 'temp' => 31.6, 'uv' => 7.6, 'o3' => 72.0],
            15 => ['pm' => 25.0, 'wind' => 12.5, 'dir' => 'NE', 'temp' => 30.8, 'uv' => 5.2, 'o3' => 64.0],
            16 => ['pm' => 23.4, 'wind' => 11.2, 'dir' => 'NE', 'temp' => 29.7, 'uv' => 2.9, 'o3' => 54.0],
            17 => ['pm' => 31.5, 'wind' => 8.4, 'dir' => 'E', 'temp' => 28.6, 'uv' => 1.0, 'o3' => 46.0],
            18 => ['pm' => 39.2, 'wind' => 6.1, 'dir' => 'ESE', 'temp' => 27.8, 'uv' => 0.0, 'o3' => 40.0],
            19 => ['pm' => 43.0, 'wind' => 5.2, 'dir' => 'SE', 'temp' => 27.2, 'uv' => 0.0, 'o3' => 36.0],
            20 => ['pm' => 41.5, 'wind' => 4.8, 'dir' => 'SE', 'temp' => 26.8, 'uv' => 0.0, 'o3' => 34.0],
            21 => ['pm' => 38.8, 'wind' => 4.5, 'dir' => 'SSE', 'temp' => 26.4, 'uv' => 0.0, 'o3' => 32.0],
            22 => ['pm' => 40.2, 'wind' => 4.0, 'dir' => 'S', 'temp' => 26.0, 'uv' => 0.0, 'o3' => 30.0],
            23 => ['pm' => 42.1, 'wind' => 3.7, 'dir' => 'S', 'temp' => 25.7, 'uv' => 0.0, 'o3' => 29.0],
        ];

        $days = [];
        for ($d = 0; $d < 3; $d++) {
            $dateObj = Carbon::today('Asia/Jakarta')->addDays($d);
            $dayVariance = ($d === 1) ? -2.5 : (($d === 2) ? 1.8 : 0.0);
            $hours = [];

            foreach ($hourlyProfile as $hr => $base) {
                $pm25 = max(8.0, round($base['pm'] + $corridorOffset + $dayVariance, 1));
                $pm10 = round($pm25 * 1.42, 1);
                $o3 = round(max(15.0, $base['o3'] + ($corridorOffset * 0.5)), 1);
                $windKph = round(max(2.0, $base['wind'] - ($corridorOffset * 0.15)), 1);
                $tempC = round($base['temp'], 1);
                $uv = round($base['uv'], 1);
                $timeStr = $dateObj->copy()->setHour($hr)->setMinute(0)->format('Y-m-d H:i');

                $hours[] = [
                    'time' => $timeStr,
                    'hour' => $hr,
                    'label' => sprintf('%02d:00 - %02d:00 %s', $hr, ($hr + 1) % 24, $hr < 12 ? 'AM' : 'PM'),
                    'temp_c' => $tempC,
                    'temp_f' => round(($tempC * 9 / 5) + 32, 1),
                    'humidity' => max(52, min(88, (int) round(82 - ($tempC - 25) * 3.5))),
                    'wind_kph' => $windKph,
                    'wind_mph' => round($windKph * 0.621371, 1),
                    'wind_dir' => $base['dir'],
                    'wind_degree' => 315,
                    'uv' => $uv,
                    'condition_text' => $hr >= 9 && $hr <= 16 ? 'Coastal Breeze & Clear' : 'Surface Inversion Haze',
                    'pm2_5' => $pm25,
                    'pm10' => $pm10,
                    'o3' => $o3,
                    'no2' => round($pm25 * 0.48, 1),
                    'so2' => 7.4,
                    'co' => round(320 + ($pm25 * 4.5), 1),
                    'us_epa_index' => $this->dosimetry->pm25ToEpaIndex($pm25),
                    'gb_defra_index' => $this->dosimetry->pm25ToDefraIndex($pm25),
                    'us_aqi' => $this->dosimetry->pm25ToUsAqi($pm25),
                    'regime_tag' => $this->describeAtmosphericRegime($hr, $pm25, $windKph, $uv),
                ];
            }

            $days[] = [
                'date' => $dateObj->toDateString(),
                'day_label' => $d === 0 ? 'Today' : ($d === 1 ? 'Tomorrow' : $dateObj->format('l')),
                'hours' => $hours,
            ];
        }

        $baselineHour = $days[0]['hours'][6];

        return [
            'source' => 'deterministic_model',
            'cached_at' => now()->toIso8601String(),
            'location' => [
                'name' => $locationLabel ?: $this->resolveLocationName($lat, $lon),
                'latitude' => $lat,
                'longitude' => $lon,
                'tz_id' => 'Asia/Jakarta',
            ],
            'current' => [
                'timestamp' => Carbon::today('Asia/Jakarta')->setHour(6)->toIso8601String(),
                'temp_c' => $baselineHour['temp_c'],
                'temp_f' => $baselineHour['temp_f'],
                'humidity' => $baselineHour['humidity'],
                'wind_kph' => $baselineHour['wind_kph'],
                'wind_mph' => $baselineHour['wind_mph'],
                'wind_dir' => $baselineHour['wind_dir'],
                'wind_degree' => $baselineHour['wind_degree'],
                'uv' => $baselineHour['uv'],
                'pm2_5' => $baselineHour['pm2_5'],
                'pm10' => $baselineHour['pm10'],
                'o3' => $baselineHour['o3'],
                'no2' => $baselineHour['no2'],
                'so2' => $baselineHour['so2'],
                'co' => $baselineHour['co'],
                'us_epa_index' => $baselineHour['us_epa_index'],
                'gb_defra_index' => $baselineHour['gb_defra_index'],
                'us_aqi' => $baselineHour['us_aqi'],
            ],
            'days' => $days,
        ];
    }

    public function describeAtmosphericRegime(int $hour, float $pm25, float $windKph, float $uv): string
    {
        if ($pm25 >= 50.0 && $windKph < 5.5) {
            return 'High Inhaled Load • Stagnant';
        }

        if ($hour >= 6 && $hour <= 7 && $pm25 >= 42.0) {
            return 'Commuter Peak Surge • Heavy Fleet';
        }

        if ($hour === 8 || ($pm25 > 32.0 && $pm25 <= 45.0 && $windKph >= 6.0)) {
            return 'Breeze Initiating • Mixing Layer Rises';
        }

        if ($pm25 <= 26.0 && $windKph >= 10.0) {
            return 'Peak Marine Flush • Low Direct Toxins';
        }

        if ($uv >= 7.5) {
            return 'Clear Air • Rising Solar Radiation';
        }

        if ($pm25 <= 35.0) {
            return 'Dispersed Corridor • Safe Aerobic Flow';
        }

        return 'Moderate Boundary Layer Accumulation';
    }

    public function resolveLocationName(float $lat, float $lon): string
    {
        $known = [
            ['name' => 'GBK Outer Perimeter, Jakarta', 'lat' => -6.2185, 'lon' => 106.8025],
            ['name' => 'Sudirman Corridor, Jakarta', 'lat' => -6.2088, 'lon' => 106.8228],
            ['name' => 'PIK Coastal Loop, North Jakarta', 'lat' => -6.1085, 'lon' => 106.7425],
            ['name' => 'BSD Green Boulevard, Tangerang', 'lat' => -6.3016, 'lon' => 106.6534],
            ['name' => 'UI Depok Forest Trail, Depok', 'lat' => -6.3628, 'lon' => 106.8271],
            ['name' => 'Kelapa Gading Endurance Loop, Jakarta', 'lat' => -6.1574, 'lon' => 106.9080],
        ];

        foreach ($known as $spot) {
            if (abs($spot['lat'] - $lat) < 0.025 && abs($spot['lon'] - $lon) < 0.025) {
                return $spot['name'];
            }
        }

        return sprintf('Corridor %.3f, %.3f', $lat, $lon);
    }
}
