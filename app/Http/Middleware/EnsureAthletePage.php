<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAthletePage
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isAdmin() && ($request->isMethod('get') || $request->isMethod('head')) && ! $request->expectsJson()) {
            return redirect()->route('admin.overview');
        }

        return $next($request);
    }
}
