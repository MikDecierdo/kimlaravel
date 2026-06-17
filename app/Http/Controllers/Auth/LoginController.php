<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Staff;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /**
     * Show admin login form
     */
    public function showAdminLoginForm()
    {
        // Check if already authenticated via admin guard
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.login-admin');
    }

    /**
     * Show department head login form
     */
    public function showDepartmentHeadLoginForm()
    {
        // Check if already authenticated via department_head guard
        if (Auth::guard('department_head')->check()) {
            return redirect()->route('department-head.dashboard');
        }
        return view('auth.login-department-head');
    }

    /**
     * Show student login form
     */
    public function showStudentLoginForm()
    {
        // Check if already authenticated via student guard
        if (Auth::guard('student')->check()) {
            $user = Auth::guard('student')->user();
            if ($this->isFacultyVoterByEmail($user?->email)) {
                return redirect()->route('faculty.dashboard');
            }

            return redirect()->route('dashboard');
        }
        return view('auth.login-student');
    }

    /**
     * Handle admin login
     */
    public function loginAdmin(Request $request)
    {
        return $this->handleRoleBasedLogin($request, 'admin');
    }

    /**
     * Handle department head login
     */
    public function loginDepartmentHead(Request $request)
    {
        return $this->handleRoleBasedLogin($request, 'department_head');
    }

    /**
     * Handle student login
     */
    public function loginStudent(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // If faculty exists only in staff table, mirror it into users table
        // so faculty can share the student voting portal.
        $portalUser = User::where('email', $credentials['email'])
            ->whereIn('role', ['student', 'staff'])
            ->first();

        $needsFacultySync = !$portalUser || !Hash::check($credentials['password'], $portalUser->password);

        if ($needsFacultySync) {
            $faculty = Staff::where('email', $credentials['email'])
                ->where('is_department_head', false)
                ->where(function ($query) {
                    $query->whereNull('position')
                        ->orWhereRaw('LOWER(position) in (?, ?)', ['faculty', 'none']);
                })
                ->first();

            if ($faculty && Hash::check($credentials['password'], $faculty->password)) {
                $this->syncFacultyVoterUser($faculty);
            }
        }

        return $this->handleRoleBasedLogin($request, 'student');
    }

    /**
     * Handle role-based login with session management and guard-specific authentication
     */
    protected function handleRoleBasedLogin(Request $request, string $expectedRole)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Determine the guard and the correct model based on role
        $guard = $this->getGuardForRole($expectedRole);
        $user  = $this->findUserByRole($credentials['email'], $expectedRole);

        // Validate credentials
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Invalid credentials or you do not have ' . str_replace('_', ' ', $expectedRole) . ' access.',
            ])->onlyInput('email');
        }

        // Block pending/denied student accounts
        if ($expectedRole === 'student' && isset($user->approval_status)) {
            if ($user->approval_status === 'pending') {
                return redirect()->route('login.student')
                    ->with('registration_pending', 'Your account is still pending approval by your Department Head. Please check back later.');
            }
            if ($user->approval_status === 'denied') {
                return redirect()->route('login.student')
                    ->with('account_denied', 'Your account request was denied. Please contact your Department Head for more information.');
            }
        }

        // Generate browser fingerprint
        $browserFingerprint = $this->generateBrowserFingerprint($request);

        // Check for existing active sessions
        $existingSession = UserSession::where('user_id', $user->id)
            ->where('role', $expectedRole)
            ->where('is_active', true)
            ->first();

        if ($existingSession) {
            $existingSession->invalidate();
        }

        // Create new session record
        $sessionId = Str::random(64);
        UserSession::create([
            'session_id'         => $sessionId,
            'user_id'            => $user->id,
            'role'               => $expectedRole,
            'browser_fingerprint'=> $browserFingerprint,
            'ip_address'         => $request->ip(),
            'login_timestamp'    => now(),
            'last_activity'      => now(),
            'is_active'          => true,
        ]);

        // Store session ID in secure cookie
        $request->session()->put('user_session_id', $sessionId);
        $request->session()->put('browser_fingerprint', $browserFingerprint);

        // Authenticate using the specific guard
        Auth::guard($guard)->login($user);
        $request->session()->regenerate();

        // Redirect based on role
        return $this->redirectBasedOnRole($expectedRole);
    }

    /**
     * Resolve the correct model for the given role.
     * Admin  → admins table  (Admin model)
     * Department Head → staff table (Staff model)
     * Student → users table  (User model)
     */
    protected function findUserByRole(string $email, string $role)
    {
        return match($role) {
            'admin'           => Admin::where('email', $email)->first(),
            'department_head' => Staff::where('email', $email)
                ->where(function ($query) {
                    $query->where('is_department_head', true)
                        ->orWhere('can_access_department_portal', true);
                })
                ->first(),
            'student'         => User::where('email', $email)
                ->whereIn('role', ['student', 'staff'])
                ->first(),
            default           => User::where('email', $email)->where('role', $role)->first(),
        };
    }

    /**
     * Get the guard name for a given role
     */
    protected function getGuardForRole(string $role): string
    {
        return match($role) {
            'admin' => 'admin',
            'department_head' => 'department_head',
            'student' => 'student',
            default => 'web',
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
     * Redirect user based on role
     */
    protected function redirectBasedOnRole(string $role)
    {
        switch ($role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'department_head':
                return redirect()->route('department-head.dashboard');
            case 'student':
                $user = Auth::guard('student')->user();
                if ($this->isFacultyVoterByEmail($user?->email)) {
                    return redirect()->route('faculty.dashboard');
                }
                return redirect()->route('dashboard');
            default:
                return redirect('/');
        }
    }

    private function syncFacultyVoterUser(Staff $faculty): User
    {
        $facultyVoterId = 'FAC-' . str_pad((string) $faculty->id, 6, '0', STR_PAD_LEFT);

        $payload = [
            'name' => $faculty->name ?: 'Faculty',
            'middle_name' => $faculty->middle_name,
            'last_name' => $faculty->last_name ?: 'none',
            'password' => $faculty->password,
            'department' => $faculty->department ?: 'none',
            'student_id' => $facultyVoterId,
            'year_level' => 'none',
            'profile_picture' => $faculty->profile_picture,
            'approval_status' => 'approved',
        ];

        try {
            return User::updateOrCreate(
                ['email' => $faculty->email],
                $payload + ['role' => 'staff']
            );
        } catch (QueryException $e) {
            // Older databases may not include 'staff' in users.role enum.
            // Fallback to 'student' while still treating account as faculty via staff table lookup.
            return User::updateOrCreate(
                ['email' => $faculty->email],
                $payload + ['role' => 'student']
            );
        }
    }

    private function isFacultyVoterByEmail(?string $email): bool
    {
        if (!$email) {
            return false;
        }

        return Staff::where('email', $email)
            ->where('is_department_head', false)
            ->where(function ($query) {
                $query->whereNull('position')
                    ->orWhereRaw('LOWER(position) in (?, ?)', ['faculty', 'none']);
            })
            ->exists();
    }

    /**
     * Handle logout with session cleanup
     */
    public function logout(Request $request)
    {
        $sessionId = $request->session()->get('user_session_id');

        if ($sessionId) {
            // Mark session as inactive
            UserSession::where('session_id', $sessionId)
                ->update(['is_active' => false]);
        }

        // Determine which guard is currently authenticated and logout
        $guards = ['admin', 'department_head', 'student', 'web'];
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
                break;
            }
        }

        // Invalidate and regenerate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.student');
    }
}
