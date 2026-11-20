<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Your account has been deactivated. Contact an administrator.',
                    'redirect' => route('login'),
                    'force_logout' => true,
                ], 401);
            }

            return redirect()->route('login')
                ->withErrors(['loginname' => 'Your account has been deactivated. Contact an administrator.']);
        }

        return $next($request);
    }
}
