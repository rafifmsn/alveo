<?php

namespace Database\Seeders;

use App\Models\ClubBroadcast;
use App\Models\SavedRoute;
use App\Models\User;
use App\Models\WorkoutLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = strtolower((string) config('app.seed.admin.email', 'admin@alveo.id'));
        $adminName = (string) config('app.seed.admin.name', 'Alveo Admin');
        $adminPassword = (string) config('app.seed.admin.password', 'password');

        User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'email_verified_at' => now(),
                'role' => User::ROLE_ADMIN,
                'password' => Hash::make($adminPassword),
                'sensitivity_profile' => 'standard',
                'unit_system' => 'metric',
                'default_activity' => 'running',
                'default_duration' => 60,
                'dark_mode' => false,
            ]
        );

        $userEmail = strtolower((string) config('app.seed.user.email', 'rafif@alveo.id'));
        $userName = (string) config('app.seed.user.name', 'Rafif Muchsin');
        $userPassword = (string) config('app.seed.user.password', 'password');

        $member = User::firstOrCreate(
            ['email' => $userEmail],
            [
                'name' => $userName,
                'email_verified_at' => now(),
                'role' => User::ROLE_USER,
                'password' => Hash::make($userPassword),
                'sensitivity_profile' => 'standard',
                'unit_system' => 'metric',
                'default_activity' => 'running',
                'default_duration' => 60,
                'dark_mode' => false,
            ]
        );

        $presets = [
            [
                'name' => 'GBK Outer Perimeter, Jakarta',
                'region' => 'Senayan, Central Jakarta',
                'latitude' => -6.2185,
                'longitude' => 106.8025,
                'distance_km' => 4.2,
                'preferred_activity' => 'running',
                'surface_type' => 'Tree-Lined Tarmac Loop',
            ],
            [
                'name' => 'Sudirman Corridor, Jakarta',
                'region' => 'SCBD - Bundaran HI, Central Jakarta',
                'latitude' => -6.2088,
                'longitude' => 106.8228,
                'distance_km' => 12.5,
                'preferred_activity' => 'cycling',
                'surface_type' => 'Arterial Protected Bike Lane',
            ],
            [
                'name' => 'PIK Coastal Loop, North Jakarta',
                'region' => 'Pantai Indah Kapuk 2',
                'latitude' => -6.1085,
                'longitude' => 106.7425,
                'distance_km' => 25.0,
                'preferred_activity' => 'cycling',
                'surface_type' => 'Coastal Sea-Breeze Causeway',
            ],
        ];

        $gbkRoute = null;
        foreach ($presets as $p) {
            $route = SavedRoute::firstOrCreate(
                ['name' => $p['name'], 'is_preset' => true],
                array_merge($p, ['is_preset' => true, 'user_id' => null])
            );
            if ($gbkRoute === null) {
                $gbkRoute = $route;
            }
        }

        if (WorkoutLog::where('user_id', $member->id)->count() === 0) {
            WorkoutLog::create([
                'user_id' => $member->id,
                'saved_route_id' => $gbkRoute?->id,
                'location_name' => 'GBK Outer Perimeter, Jakarta',
                'activity_type' => 'running',
                'exertion_zone' => 3,
                'duration_minutes' => 60,
                'ambient_pm2_5' => 22.1,
                'ambient_o3' => 48.0,
                'ventilation_rate_lpm' => 66.0,
                'air_volume_m3' => 3.96,
                'inhaled_dose_ug' => 65.6,
                'exertion_risk_score' => 3.1,
                'trained_at' => now()->subDays(1)->setHour(9)->setMinute(0),
                'notes' => 'Shifted tempo session from 06:00 to 09:00 AM window; noticeably cleaner coastal flush.',
            ]);

            WorkoutLog::create([
                'user_id' => $member->id,
                'saved_route_id' => $gbkRoute?->id,
                'location_name' => 'PIK Coastal Loop, North Jakarta',
                'activity_type' => 'cycling',
                'exertion_zone' => 2,
                'duration_minutes' => 90,
                'ambient_pm2_5' => 18.4,
                'ambient_o3' => 44.0,
                'ventilation_rate_lpm' => 44.0,
                'air_volume_m3' => 3.96,
                'inhaled_dose_ug' => 49.5,
                'exertion_risk_score' => 2.6,
                'trained_at' => now()->subDays(3)->setHour(8)->setMinute(30),
                'notes' => 'Steady Zone 2 coastal ride with strong NNW onshore breeze.',
            ]);

            WorkoutLog::create([
                'user_id' => $member->id,
                'saved_route_id' => $gbkRoute?->id,
                'location_name' => 'Sudirman Corridor, Jakarta',
                'activity_type' => 'running',
                'exertion_zone' => 4,
                'duration_minutes' => 45,
                'ambient_pm2_5' => 46.8,
                'ambient_o3' => 52.0,
                'ventilation_rate_lpm' => 92.0,
                'air_volume_m3' => 4.14,
                'inhaled_dose_ug' => 158.9,
                'exertion_risk_score' => 6.1,
                'trained_at' => now()->subDays(5)->setHour(6)->setMinute(15),
                'notes' => 'Early morning threshold intervals before inversion cleared; high particulate load.',
            ]);
        }

        if (ClubBroadcast::count() === 0) {
            $broadcast = ClubBroadcast::create([
                'user_id' => $member->id,
                'club_name' => 'Senayan Dawn Runners',
                'captain_name' => $member->name,
                'corridor_name' => 'GBK Outer Perimeter, Jakarta',
                'latitude' => -6.2185,
                'longitude' => 106.8025,
                'activity_type' => 'running',
                'duration_minutes' => 60,
                'verdict' => 'DELAY',
                'window_start' => '09:00',
                'window_end' => '10:00 AM',
                'session_date' => now()->toDateString(),
                'projected_pm2_5' => 22.1,
                'improvement_pct' => 62.2,
                'wind_summary' => '12.8 km/h NNW Coastal Flush',
                'captain_note' => 'Morning inversion is holding 58 ug/m3 at 06:00 AM. Pushing group tempo run to 09:00 AM Gate 7 assembly to cut inhaled PM2.5 by 62%.',
                'followers_count' => 1,
            ]);

            DB::table('club_broadcast_follows')->insert([
                'user_id' => $member->id,
                'club_broadcast_id' => $broadcast->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
