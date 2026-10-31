<?php

namespace Tests\Feature;

use App\Models\SavedRoute;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlveoApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_landing_page_renders_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Train Smarter')
            ->assertSee('Connects with Your Tools')
            ->assertSee('/images/landing/hero-dashboard.svg')
            ->assertSee('/images/landing/tools-bg.svg');
    }

    public function test_newly_registered_user_is_unverified_and_redirected_to_verify_screen(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@alveo.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/verify');

        $user = User::where('email', 'budi@alveo.id')->firstOrFail();
        $this->assertNull($user->email_verified_at);

        // Attempting to visit dashboard redirects to /verify
        $this->actingAs($user)->get('/finder')->assertRedirect('/verify');

        // Visiting /verify shows Verification Required modal & mailto link
        $verifyPage = $this->actingAs($user)->get('/verify');
        $verifyPage->assertOk();
        $verifyPage->assertSee('Verification Required');
        $verifyPage->assertSee('24-48 hours');
        $verifyPage->assertSee('mailto:');
    }

    public function test_verified_user_can_access_all_six_blade_screens(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'rafif@alveo.id')->firstOrFail();
        $this->assertNotNull($user->email_verified_at);

        // Screen 1: Window Finder
        $this->actingAs($user)->get('/finder')
            ->assertOk()
            ->assertSee('Optimal Rescheduled Window')
            ->assertSee('Microsoft 365')
            ->assertSee('Outlook.com')
            ->assertSee('Yahoo Cal')
            ->assertSee('Window Timeline');

        // Screen 2: Saved Routes & Hotspots
        $this->actingAs($user)->get('/routes')
            ->assertOk()
            ->assertSee('GBK Outer Perimeter, Jakarta');

        // Screen 3: Inhaled Dosimetry Calculator
        $this->actingAs($user)->get('/dosimetry')
            ->assertOk()
            ->assertSee('Inhaled Dosimetry Calculator');

        // Screen 4: Exposure History & Training Log
        $this->actingAs($user)->get('/history')
            ->assertOk()
            ->assertSee('Recorded Outdoor Sessions');

        // Screen 5: Club Broadcasts
        $this->actingAs($user)->get('/broadcasts')
            ->assertOk()
            ->assertSee('Senayan Dawn Runners');

        // Screen 6: Settings
        $this->actingAs($user)->get('/settings')
            ->assertOk()
            ->assertSee('Threshold Calibration');
    }

    public function test_user_can_create_route_log_workout_and_publish_broadcast(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('email', 'rafif@alveo.id')->firstOrFail();
        $presetRoute = SavedRoute::firstOrFail();

        // Save custom route
        $this->actingAs($user)->post('/routes', [
            'name' => 'Kemayoran Runway Strip',
            'region' => 'Central Jakarta',
            'latitude' => -6.1560,
            'longitude' => 106.8460,
            'distance_km' => 7.5,
            'preferred_activity' => 'cycling',
            'surface_type' => 'Wide Boulevard',
        ])->assertRedirect('/routes');

        $this->assertDatabaseHas('saved_routes', ['name' => 'Kemayoran Runway Strip']);

        // Log completed workout
        $this->actingAs($user)->post('/history', [
            'saved_route_id' => $presetRoute->id,
            'activity_type' => 'running',
            'exertion_zone' => 3,
            'duration_minutes' => 60,
            'ambient_pm2_5' => 24.0,
            'trained_date' => now()->toDateString(),
            'trained_time' => '09:00',
            'notes' => 'Clean morning run',
        ])->assertRedirect('/history');

        $this->assertDatabaseHas('workout_logs', ['notes' => 'Clean morning run']);

        // Publish club broadcast
        $this->actingAs($user)->post('/broadcasts', [
            'club_name' => 'Menteng Tempo Crew',
            'saved_route_id' => $presetRoute->id,
            'activity_type' => 'running',
            'duration_minutes' => 60,
            'session_date' => now()->toDateString(),
            'captain_note' => 'Group roll out at 09:00 AM sharp.',
        ])->assertRedirect('/broadcasts');

        $this->assertDatabaseHas('club_broadcasts', ['club_name' => 'Menteng Tempo Crew']);
    }

    public function test_api_v1_evaluate_window_returns_expected_contract(): void
    {
        $response = $this->postJson('/api/v1/evaluate-window', [
            'latitude' => -6.2185,
            'longitude' => 106.8025,
            'activity_type' => 'running',
            'duration_minutes' => 60,
            'include_ai_brief' => true,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'current_conditions' => [
                    'timestamp',
                    'pm2_5',
                    'us_aqi',
                    'wind_speed_kmh',
                    'exertion_risk_score',
                ],
                'recommendation' => [
                    'best_start_time',
                    'best_end_time',
                    'projected_pm2_5',
                    'improvement_pct',
                ],
                'ai_briefing',
                'actions' => [
                    'google_calendar_url',
                    'm365_calendar_url',
                    'outlook_calendar_url',
                    'yahoo_calendar_url',
                    'telegram_share_url',
                    'mailto_share_url',
                    'ics_download_payload',
                ],
            ]);
    }
}
