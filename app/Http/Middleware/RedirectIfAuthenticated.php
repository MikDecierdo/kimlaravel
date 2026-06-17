<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Redirect based on the authenticated guard
                return match($guard) {
                    'admin' => redirect()->route('admin.dashboard'),
                    'department_head' => redirect()->route('department-head.dashboard'),
                    'student' => redirect()->route('dashboard'),
                    default => redirect(RouteServiceProvider::HOME),
                };
            }
        }

        return $next($request);
    }
}
