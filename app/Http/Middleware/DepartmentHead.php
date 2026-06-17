<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DepartmentHead
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Being authenticated via the 'department_head' guard is sufficient —
        // Staff model has no role column; the guard itself enforces the role.
        if (Auth::guard('department_head')->check()) {
            $staff = Auth::guard('department_head')->user();
            if ($staff && ((bool) ($staff->is_department_head ?? false) || (bool) ($staff->can_access_department_portal ?? false))) {
                return $next($request);
            }
        }

        Auth::guard('department_head')->logout();
        return redirect()->route('login.department-head')->with('error', 'Please login as a department head or authorized faculty.');
    }
}
