<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $cacheEntriesCount = 0;
        if (Schema::hasTable('cache')) {
            $cacheEntriesCount = DB::table('cache')->count();
        }

        return view('pages.settings', [
            'user' => $user,
            'cacheEntriesCount' => $cacheEntriesCount,
            'weatherApiConfigured' => config('services.weatherapi.key', '') !== '',
            'cacheTtlMinutes' => (int) round(config('services.weatherapi.cache_ttl', 2700) / 60),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'sensitivity_profile' => ['required', 'string', 'in:sensitive,standard,tolerant'],
            'unit_system' => ['required', 'string', 'in:metric,imperial'],
            'default_activity' => ['required', 'string', 'in:running,cycling,walking'],
            'default_duration' => ['required', 'integer', 'in:30,60,90,120'],
            'dark_mode' => ['nullable', 'boolean'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->sensitivity_profile = $validated['sensitivity_profile'];
        $user->unit_system = $validated['unit_system'];
        $user->default_activity = $validated['default_activity'];
        $user->default_duration = (int) $validated['default_duration'];
        $user->dark_mode = $request->boolean('dark_mode');

        if (! empty($validated['new_password'])) {
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();

        return redirect()->route('settings.index')
            ->with('status', 'Preferences and threshold calibration saved.');
    }
}
