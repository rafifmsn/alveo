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
            ->assertSee('/images/landing/window-finder.png')
            ->assertSee('/images/landing/exposure-planning.png')
            ->assertSee('/images/landing/dosimetry-guidance.png')
            ->assertSee('/images/landing/tool_sync.png')
            ->assertSee('/images/landing/tools-bg.svg');
    }

    public function test_login_screen_does_not_prefill_credentials(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertDontSee('value="rafif@alveo.id"', false)
            ->assertDontSee('value="password"', false);
    }

    public function test_newly_registered_user_is_verified_by_default_and_redirected_to_finder(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@alveo.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/finder');

        $user = User::where('email', 'budi@alveo.id')->firstOrFail();
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame(User::ROLE_USER, $user->role);

        $this->actingAs($user)->get('/finder')->assertOk();
    }

    public function test_unverified_user_is_gated_at_verify_screen(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/finder')->assertRedirect('/verify');

        $verifyPage = $this->actingAs($user)->get('/verify');
        $verifyPage->assertOk()
            ->assertSee('Verification Required')
            ->assertSee('24-48 hours')
            ->assertSee('mailto:');
    }

    public function test_seeder_derives_accounts_from_config_with_three_presets_and_single_follow_broadcast(): void
    {
        config([
            'app.seed.admin.email' => 'custom-admin@alveo.id',
            'app.seed.admin.password' => 'adminsecret',
            'app.seed.user.email' => 'custom-athlete@alveo.id',
            'app.seed.user.password' => 'usersecret',
        ]);

        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'custom-admin@alveo.id')->firstOrFail();
        $member = User::where('email', 'custom-athlete@alveo.id')->firstOrFail();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($member->isAdmin());
        $this->assertSame(3, SavedRoute::where('is_preset', true)->count());
        $this->assertDatabaseCount('club_broadcasts', 1);
        $this->assertDatabaseCount('club_broadcast_follows', 1);
        $this->assertDatabaseHas('club_broadcasts', [
            'user_id' => $member->id,
            'followers_count' => 1,
        ]);
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
            ->assertSee('Outlook')
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

        // Logging workout outside the 3-day window fails validation
        $this->actingAs($user)->post('/history', [
            'saved_route_id' => $presetRoute->id,
            'activity_type' => 'running',
            'exertion_zone' => 3,
            'duration_minutes' => 60,
            'trained_date' => now('Asia/Jakarta')->subDays(7)->toDateString(),
            'trained_time' => '09:00',
        ])->assertSessionHasErrors('trained_date');

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

    public function test_api_v1_locations_searches_indonesia_divisions_deterministically(): void
    {
        $banten = $this->getJson('/api/v1/locations?q=Ban');
        $banten->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'level', 'type', 'name', 'name_en', 'label', 'lat', 'lon'],
                ],
            ]);

        $first = $banten->json('data.0');
        $this->assertSame('Banten', $first['name']);
        $this->assertSame(1, $first['level']);

        $jakarta = $this->getJson('/api/v1/locations?q=Jaka');
        $jakarta->assertOk();
        $labels = collect($jakarta->json('data'))->pluck('label')->implode(' | ');
        $this->assertStringContainsString('Jakarta', $labels);
    }

    public function test_finder_resolves_administrative_location_and_uses_sticky_viewport_sidebar(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get('/finder?location=Banten');
        $response->assertOk()
            ->assertSee('Banten')
            ->assertSee('18:00 - 19:00 PM')
            ->assertSee('lg:h-screen lg:sticky lg:top-0', false);
    }

    public function test_calendar_ics_supports_custom_selected_slot(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get('/calendar/ics?lat=-6.2185&lon=106.8025&activity=running&duration=60&location=GBK&start_iso=2026-11-20T16:00:00%2B07:00&end_iso=2026-11-20T17:00:00%2B07:00&pm2_5=24.5');
        $response->assertOk()
            ->assertSee('DTSTART:20261120T090000Z')
            ->assertSee('DTEND:20261120T100000Z');
    }

    public function test_role_separation_redirects_and_admin_user_management(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@alveo.id')->firstOrFail();
        $athlete = User::where('email', 'rafif@alveo.id')->firstOrFail();
        $extra = User::factory()->create(['name' => 'Test Runner', 'email' => 'runner@alveo.id']);

        // Athlete is blocked from admin pages and admin mutations
        $this->actingAs($athlete)->get('/admin')->assertRedirect('/finder');
        $this->actingAs($athlete)->get('/admin/users')->assertRedirect('/finder');
        $this->actingAs($athlete)->get('/admin/system')->assertRedirect('/finder');
        $this->actingAs($athlete)->patch("/admin/users/{$extra->id}/role", ['role' => 'admin'])->assertForbidden();
        $this->actingAs($athlete)->delete("/admin/users/{$extra->id}")->assertForbidden();
        $this->actingAs($athlete)->post('/admin/system/reset', ['confirmation' => 'RESET'])->assertForbidden();

        // Admin is auto-redirected away from athlete pages to /admin
        $this->actingAs($admin)->get('/finder')->assertRedirect('/admin');
        $this->actingAs($admin)->get('/routes')->assertRedirect('/admin');
        $this->actingAs($admin)->get('/settings')->assertRedirect('/admin');

        $expectedApiBadge = config('services.visualcrossing.key', '') !== '' ? 'Live API Key Active' : 'Deterministic Engine Active';

        // Admin views overview and user management without athlete nav
        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('System Aggregate Telemetry')
            ->assertSee('User Management')
            ->assertSee($expectedApiBadge)
            ->assertSee('handleWorkspaceClick')
            ->assertDontSee('Window Finder');

        $this->actingAs($admin)->get('/admin/users')
            ->assertOk()
            ->assertSee('runner@alveo.id');

        // Promote and demote extra user
        $this->actingAs($admin)->patch("/admin/users/{$extra->id}/role", ['role' => 'admin'])
            ->assertRedirect('/admin/users');
        $this->assertTrue($extra->fresh()->isAdmin());

        $this->actingAs($admin)->patch("/admin/users/{$extra->id}/role", ['role' => 'user'])
            ->assertRedirect('/admin/users');
        $this->assertFalse($extra->fresh()->isAdmin());

        // Self-lockout guard prevents self-demotion and self-deletion
        $this->actingAs($admin)->patch("/admin/users/{$admin->id}/role", ['role' => 'user'])
            ->assertSessionHasErrors('role');
        $this->assertTrue($admin->fresh()->isAdmin());

        $this->actingAs($admin)->delete("/admin/users/{$admin->id}")
            ->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        // Admin can delete another user
        $this->actingAs($admin)->delete("/admin/users/{$extra->id}")
            ->assertRedirect('/admin/users');
        $this->assertDatabaseMissing('users', ['id' => $extra->id]);
    }

    public function test_admin_can_clear_cache_and_reset_system_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@alveo.id')->firstOrFail();
        $extra = User::factory()->create(['email' => 'temporary@alveo.id']);
        SavedRoute::create([
            'user_id' => $extra->id,
            'name' => 'Temporary Route',
            'region' => 'Jakarta',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'distance_km' => 5.0,
            'preferred_activity' => 'running',
            'surface_type' => 'Road',
            'is_preset' => false,
        ]);

        $expectedApiBadge = config('services.visualcrossing.key', '') !== '' ? 'Live API Key Active' : 'Deterministic Engine Active';

        $this->actingAs($admin)->get('/admin/system')
            ->assertOk()
            ->assertSee('Reset System Data')
            ->assertSee($expectedApiBadge);

        $this->actingAs($admin)->post('/admin/system/clear-cache')->assertRedirect('/admin/system');

        $this->actingAs($admin)->post('/admin/system/reset', ['confirmation' => 'WRONG'])
            ->assertSessionHasErrors('confirmation');
        $this->assertDatabaseHas('users', ['email' => 'temporary@alveo.id']);

        $this->actingAs($admin)->post('/admin/system/reset', ['confirmation' => 'RESET'])
            ->assertRedirect('/admin');

        $this->assertDatabaseMissing('users', ['email' => 'temporary@alveo.id']);
        $this->assertDatabaseMissing('saved_routes', ['name' => 'Temporary Route']);
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
        $this->assertSame(3, SavedRoute::where('is_preset', true)->count());
        $this->assertDatabaseCount('club_broadcasts', 1);
        $this->assertDatabaseCount('club_broadcast_follows', 1);
    }
}
