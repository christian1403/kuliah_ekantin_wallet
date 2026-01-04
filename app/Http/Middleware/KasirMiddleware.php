<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class KasirMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'error' => 'Please login to access this resource.'
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Please login to access this resource.');
        }

        $user = Auth::user();

        // Check if user has kasir role
        if (!$user->hasRole('kasir')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden.',
                    'error' => 'You do not have permission to access this resource.'
                ], 403);
            }

            // Redirect based on user's role
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'You do not have kasir access.');
            }

            if ($user->hasRole('mahasiswa')) {
                return redirect()->route('mahasiswa.dashboard')
                    ->with('error', 'You do not have kasir access.');
            }

            // Default redirect for users without specific roles
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this resource.');
        }

        // Check if kasir profile exists
        if (!$user->kasir) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Profile Not Found.',
                    'error' => 'Kasir profile not found. Please contact administrator.'
                ], 422);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Kasir profile not found. Please contact administrator.');
        }

        return $next($request);
    }
}
