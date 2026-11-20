<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            if ($request->routeIs('password.force.*', 'logout', 'theme.update')) {
                return $next($request);
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'You must change your temporary password before continuing.',
                    'redirect' => route('password.force.edit'),
                ], 403);
            }

            return redirect()->route('password.force.edit')
                ->with('info', 'For your security, please set a new personal password before accessing the system.');
        }

        return $next($request);
    }
}
