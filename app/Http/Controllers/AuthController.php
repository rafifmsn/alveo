<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = $request->user();
            if (! $user->isVerified()) {
                return redirect()->route('verification.notice');
            }

            return redirect()->intended(route('finder'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:180', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'email_verified_at' => null,
            'password' => Hash::make($validated['password']),
            'sensitivity_profile' => 'standard',
            'unit_system' => 'metric',
            'default_activity' => 'running',
            'default_duration' => 60,
            'dark_mode' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }

    public function showVerify(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user && $user->isVerified()) {
            return redirect()->route('finder');
        }

        $adminEmail = config('app.admin_contact_email', 'admin@alveo.id');
        $subject = rawurlencode('Alveo Verification Request: '.($user?->email ?? ''));
        $body = rawurlencode(
            "Hello Alveo Administrator,\n\n"
            ."Please verify my Alveo athlete account so I can access the Clean-Air Exposure Engine.\n\n"
            .'Name: '.($user?->name ?? '')."\n"
            .'Registered Email: '.($user?->email ?? '')."\n"
            .'Requested At: '.now()->toDateTimeString()." WIB\n\n"
            .'Thank you.'
        );

        $mailtoUrl = "mailto:{$adminEmail}?subject={$subject}&body={$body}";

        return view('auth.verify', [
            'user' => $user,
            'adminEmail' => $adminEmail,
            'mailtoUrl' => $mailtoUrl,
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
