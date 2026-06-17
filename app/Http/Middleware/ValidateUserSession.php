<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ValidateUserSession
{
    /**
     * Handle an incoming request.
     *
     * Validates:
     * - Session existence in database
     * - Session is active
     * - Browser fingerprint matches
     * - Session hasn't expired
     */
    public function handle(Request $request, Closure $next, ?string $expectedGuard = null): Response
    {
        // Skip validation for login routes
        if ($request->routeIs('login.*') || $request->routeIs('register')) {
            return $next($request);
        }

        // Resolve authenticated user based on expected guard when provided.
        $guard = $this->getCurrentGuard($expectedGuard);
        $user = $this->getAuthenticatedUser($guard);

        // Check if user is authenticated
        if (!$user || !$guard) {
            return $this->redirectToLogin($request, $expectedGuard);
        }

        $sessionId = $request->session()->get('user_session_id');
        $storedFingerprint = $request->session()->get('browser_fingerprint');

        // Validate session ID exists
        if (!$sessionId) {
            return $this->invalidateAndRedirect($request, 'No session found', $guard);
        }

        // Get session from database
        $userSession = UserSession::where('session_id', $sessionId)
            ->where('user_id', $user->id)
            ->where('role', $this->guardToRole($guard))
            ->where('is_active', true)
            ->first();

        // Validate session exists and is active
        if (!$userSession) {
            return $this->invalidateAndRedirect($request, 'Invalid or inactive session', $guard);
        }

        // Generate current browser fingerprint
        $currentFingerprint = $this->generateBrowserFingerprint($request);

        // Validate browser fingerprint matches (cross-browser prevention)
        if ($currentFingerprint !== $userSession->browser_fingerprint || 
            $currentFingerprint !== $storedFingerprint) {
            $userSession->invalidate();
            return $this->invalidateAndRedirect($request, 'Browser fingerprint mismatch', $guard);
        }

        // Check if session has expired (30 minutes of inactivity)
        if ($userSession->isExpired()) {
            $userSession->invalidate();
            return $this->invalidateAndRedirect($request, 'Session expired due to inactivity', $guard);
        }

        // Validate IP address consistency (optional additional security)
        if ($userSession->ip_address !== $request->ip()) {
            // Log suspicious activity but don't block (IP can change with mobile networks)
            \Log::warning('IP address changed for session', [
                'user_id' => $user->id,
                'old_ip' => $userSession->ip_address,
                'new_ip' => $request->ip()
            ]);
        }

        // Update last activity
        $userSession->updateActivity();

        return $next($request);
    }

    /**
     * Get the currently authenticated user from any guard
     */
    protected function getAuthenticatedUser(?string $guard = null)
    {
        if ($guard) {
            return Auth::guard($guard)->check() ? Auth::guard($guard)->user() : null;
        }

        foreach (['admin', 'department_head', 'student', 'web'] as $guard) {
            if (Auth::guard($guard)->check()) {
                return Auth::guard($guard)->user();
            }
        }
        return null;
    }

    /**
     * Get the current guard name
     */
    protected function getCurrentGuard(?string $expectedGuard = null)
    {
        if ($expectedGuard) {
            return Auth::guard($expectedGuard)->check() ? $expectedGuard : null;
        }

        foreach (['admin', 'department_head', 'student', 'web'] as $guard) {
            if (Auth::guard($guard)->check()) {
                return $guard;
            }
        }
        return null;
    }

    /**
     * Map guard to session role value.
     */
    protected function guardToRole(string $guard): string
    {
        return match($guard) {
            'admin' => 'admin',
            'department_head' => 'department_head',
            default => 'student',
        };
    }

    /**
     * Generate browser fingerprint
     */
    protected function generateBrowserFingerprint(Request $request)
    {
        $userAgent = $request->header('User-Agent');
        $acceptLanguage = $request->header('Accept-Language');
        $acceptEncoding = $request->header('Accept-Encoding');
        
        return hash('sha256', $userAgent . $acceptLanguage . $acceptEncoding);
    }

    /**
     * Invalidate session and redirect to login
     */
    protected function invalidateAndRedirect(Request $request, string $reason, string $guard = null)
    {
        $user = $this->getAuthenticatedUser();
        \Log::info('Session invalidated: ' . $reason, ['user_id' => $user?->id]);

        // Determine active guard before logout for redirect fallback
        $activeGuard = $guard ?? $this->getCurrentGuard() ?? 'student';

        // Logout from the specific guard
        if ($guard) {
            Auth::guard($guard)->logout();
        } else {
            foreach (['admin', 'department_head', 'student', 'web'] as $g) {
                if (Auth::guard($g)->check()) {
                    Auth::guard($g)->logout();
                }
            }
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->redirectToLogin($request, $activeGuard);
    }

    /**
     * Redirect to appropriate login based on request path or guard name.
     * Uses guard name instead of user->role (Admin/Staff have no role column).
     */
    protected function redirectToLogin(Request $request = null, string $guardOrRole = null)
    {
        // Determine redirect based on request path
        if ($request) {
            $path = $request->path();

            if (str_starts_with($path, 'admin')) {
                return redirect()->route('login.admin')->with('error', 'Your session has expired. Please login again.');
            }

            if (str_starts_with($path, 'department-head')) {
                return redirect()->route('login.department-head')->with('error', 'Your session has expired. Please login again.');
            }
        }

        // Fallback: use guard or role string
        if (in_array($guardOrRole, ['admin'])) {
            return redirect()->route('login.admin')->with('error', 'Your session has expired. Please login again.');
        }

        if (in_array($guardOrRole, ['department_head'])) {
            return redirect()->route('login.department-head')->with('error', 'Your session has expired. Please login again.');
        }

        return redirect()->route('login.student')->with('error', 'Your session has expired. Please login again.');
    }
}
