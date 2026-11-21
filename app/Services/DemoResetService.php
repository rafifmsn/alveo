<?php

namespace App\Services;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoResetService
{
    /**
     * Restore the application database to its fresh seeded state while
     * preserving the currently authenticated administrator session.
     */
    public function reset(?User $preservingAdmin = null): void
    {
        DB::transaction(function () use ($preservingAdmin) {
            DB::table('club_broadcast_follows')->delete();
            DB::table('workout_logs')->delete();
            DB::table('club_broadcasts')->delete();
            DB::table('saved_routes')->delete();

            if (Schema::hasTable('cache')) {
                DB::table('cache')->delete();
            }

            if (Schema::hasTable('sessions')) {
                $sessions = DB::table('sessions');
                if ($preservingAdmin) {
                    $sessions->where(function ($query) use ($preservingAdmin) {
                        $query->whereNull('user_id')
                            ->orWhere('user_id', '!=', $preservingAdmin->id);
                    });
                }
                $sessions->delete();
            }

            $users = DB::table('users');
            if ($preservingAdmin) {
                $users->where('id', '!=', $preservingAdmin->id);
            }
            $users->delete();

            if ($preservingAdmin) {
                $preservingAdmin->forceFill([
                    'role' => User::ROLE_ADMIN,
                    'email_verified_at' => $preservingAdmin->email_verified_at ?? now(),
                ])->save();
            }

            app(DatabaseSeeder::class)->run();
        });

        Cache::flush();
    }
}
