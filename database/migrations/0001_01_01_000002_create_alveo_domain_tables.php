<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('region')->default('Greater Jakarta');
            $table->decimal('latitude', 8, 4);
            $table->decimal('longitude', 8, 4);
            $table->decimal('distance_km', 5, 1)->default(5.0);
            $table->string('preferred_activity')->default('running');
            $table->string('surface_type')->default('Road Loop');
            $table->boolean('is_preset')->default(false);
            $table->timestamps();
        });

        Schema::create('workout_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('saved_route_id')->nullable()->constrained()->nullOnDelete();
            $table->string('location_name');
            $table->string('activity_type')->default('running');
            $table->integer('exertion_zone')->default(3);
            $table->integer('duration_minutes')->default(60);
            $table->decimal('ambient_pm2_5', 6, 1);
            $table->decimal('ambient_o3', 6, 1)->default(45.0);
            $table->decimal('ventilation_rate_lpm', 6, 1);
            $table->decimal('air_volume_m3', 6, 2);
            $table->decimal('inhaled_dose_ug', 8, 1);
            $table->decimal('exertion_risk_score', 4, 1);
            $table->timestamp('trained_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('club_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('club_name');
            $table->string('captain_name');
            $table->string('corridor_name');
            $table->decimal('latitude', 8, 4)->default(-6.2185);
            $table->decimal('longitude', 8, 4)->default(106.8025);
            $table->string('activity_type')->default('running');
            $table->integer('duration_minutes')->default(60);
            $table->string('verdict')->default('DELAY');
            $table->string('window_start')->default('09:00 AM');
            $table->string('window_end')->default('10:00 AM');
            $table->date('session_date');
            $table->decimal('projected_pm2_5', 6, 1)->default(22.1);
            $table->decimal('improvement_pct', 5, 1)->default(62.0);
            $table->string('wind_summary')->default('11.4 km/h NW Coastal Flush');
            $table->text('captain_note');
            $table->integer('followers_count')->default(24);
            $table->timestamps();
        });

        Schema::create('club_broadcast_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('club_broadcast_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'club_broadcast_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_broadcast_follows');
        Schema::dropIfExists('club_broadcasts');
        Schema::dropIfExists('workout_logs');
        Schema::dropIfExists('saved_routes');
    }
};
