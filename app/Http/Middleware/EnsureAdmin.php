<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->isAdmin()) {
            if (! $request->isMethod('get') && ! $request->isMethod('head')) {
                abort(403, 'Administrator privileges required.');
            }

            if ($request->expectsJson()) {
                abort(403, 'Administrator privileges required.');
            }

            return redirect()->route('finder');
        }

        return $next($request);
    }
}
