<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckSessionAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is logged in via session
        if (!session()->has('user_id') || !session()->has('role')) {
            Log::info('User not logged in, redirecting to login');
            return redirect()->route('login')->with('error', 'Please login to access dashboard');
        }        // Check specific role requirements
        $path = $request->path();
        $role = session('role');
        $userId = session('user_id');

        Log::info('Session auth check - Path: ' . $path . ', Role: ' . $role . ', User ID: ' . $userId);

        // Ensure specific dashboards can only be accessed by the correct role
        if (strpos($path, 'dashboard/mcmc') === 0 && $role !== 'mcmc') {
            Log::info('Unauthorized access to MCMC dashboard by ' . $role);
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        if (strpos($path, 'dashboard/agency') === 0 && $role !== 'agency') {
            Log::info('Unauthorized access to Agency dashboard by ' . $role);
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        if (strpos($path, 'dashboard/public') === 0 && $role !== 'public') {
            Log::info('Unauthorized access to Public dashboard by ' . $role);
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }
          // For debugging purposes, log the session info for agencies
        if ($role === 'agency') {
            Log::info('Agency user accessing path: ' . $path . ', User ID: ' . $userId);
        }

        return $next($request);
    }
}
