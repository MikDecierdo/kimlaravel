<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // Determine redirect based on the route path
        $path = $request->path();

        // Admin routes redirect to admin login
        if (str_starts_with($path, 'admin')) {
            return route('login.admin');
        }

        // Department head routes redirect to department head login
        if (str_starts_with($path, 'department-head')) {
            return route('login.department-head');
        }

        // Default to student login
        return route('login.student');
    }
}
