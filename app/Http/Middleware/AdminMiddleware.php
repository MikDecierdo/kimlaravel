<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Being authenticated via the 'admin' guard is sufficient —
        // Admin model has no role column; the guard itself enforces the role.
        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

        Auth::guard('admin')->logout();
        return redirect()->route('login.admin')->with('error', 'Please login as an administrator.');
    }
}
