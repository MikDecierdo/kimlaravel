<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StudentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if authenticated via student guard
        if (Auth::guard('student')->check()) {
            // Verify the user actually has student role
            if (in_array(Auth::guard('student')->user()->role, ['student', 'staff'], true)) {
                return $next($request);
            }
        }

        // Logout from student guard and redirect to student login
        Auth::guard('student')->logout();
        return redirect()->route('login.student')->with('error', 'Please login as a student or faculty voter.');
    }
}
