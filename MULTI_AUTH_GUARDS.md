# Multiple Authentication Guards Implementation

## Overview
This Laravel application now uses **multiple authentication guards** to provide separate login sessions for different user roles. Each user type (Admin, Department Head, Student) has their own authentication context.

## What Are Authentication Guards?

Guards define **how users are authenticated** for each request. With multiple guards, different user types can:
- Have completely separate login sessions
- Be authenticated simultaneously in different browser tabs
- Have isolated authentication contexts for better security

## Configured Guards

### 1. Admin Guard (`admin`)
- **Provider**: `admins` (uses User model filtered by role)
- **Login Route**: `/login/admin`
- **Middleware**: `auth:admin`
- **Redirect**: `/admin/candidates`

### 2. Department Head Guard (`department_head`)
- **Provider**: `department_heads` (uses User model filtered by role)
- **Login Route**: `/login/department-head`
- **Middleware**: `auth:department_head`
- **Redirect**: `/department-head/dashboard`

### 3. Student Guard (`student`)
- **Provider**: `students` (uses User model filtered by role)
- **Login Route**: `/login/student`
- **Middleware**: `auth:student`
- **Redirect**: `/dashboard`

### 4. Web Guard (`web`)
- **Provider**: `users`
- **Purpose**: Fallback guard for general authentication

## Configuration

### Auth Config (`config/auth.php`)
```php
'defaults' => [
    'guard' => 'student',  // Default guard
    'passwords' => 'users',
],

'guards' => [
    'admin' => [
        'driver' => 'session',
        'provider' => 'admins',
    ],
    'department_head' => [
        'driver' => 'session',
        'provider' => 'department_heads',
    ],
    'student' => [
        'driver' => 'session',
        'provider' => 'students',
    ],
],

'providers' => [
    'admins' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
    // ... similar for other guards
],
```

## Route Protection

### Protecting Routes with Guards

```php
// Student routes protected by student guard
Route::middleware(['auth:student'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/voting', [VotingController::class, 'index']);
});

// Admin routes protected by admin guard
Route::middleware(['auth:admin'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/candidates', [AdminController::class, 'candidates']);
    });
});

// Department head routes protected by department_head guard
Route::middleware(['auth:department_head'])->group(function () {
    Route::prefix('department-head')->group(function () {
        Route::get('/dashboard', [DepartmentHeadController::class, 'dashboard']);
    });
});
```

## Login Controllers

### Guard-Specific Login
```php
// LoginController.php
protected function handleRoleBasedLogin(Request $request, string $expectedRole)
{
    // ... validate credentials ...
    
    // Get the guard for this role
    $guard = $this->getGuardForRole($expectedRole);
    
    // Authenticate using the specific guard
    Auth::guard($guard)->login($user);
    
    // ... redirect ...
}

protected function getGuardForRole(string $role): string
{
    return match($role) {
        'admin' => 'admin',
        'department_head' => 'department_head',
        'student' => 'student',
        default => 'web',
    };
}
```

## Middleware

### Guard-Specific Middleware

Each role has middleware that checks the specific guard:

**AdminMiddleware.php**
```php
public function handle(Request $request, Closure $next): Response
{
    if (Auth::guard('admin')->check()) {
        if (Auth::guard('admin')->user()->role === 'admin') {
            return $next($request);
        }
    }
    
    Auth::guard('admin')->logout();
    return redirect()->route('login.admin')
        ->with('error', 'Please login as an administrator.');
}
```

**StudentMiddleware.php**
```php
public function handle(Request $request, Closure $next): Response
{
    if (Auth::guard('student')->check()) {
        if (Auth::guard('student')->user()->role === 'student') {
            return $next($request);
        }
    }
    
    Auth::guard('student')->logout();
    return redirect()->route('login.student')
        ->with('error', 'Please login as a student.');
}
```

### Authenticate Middleware

Redirects unauthenticated users to the appropriate login page:

```php
protected function redirectTo(Request $request): ?string
{
    if ($request->expectsJson()) {
        return null;
    }

    $path = $request->path();

    if (str_starts_with($path, 'admin')) {
        return route('login.admin');
    }

    if (str_starts_with($path, 'department-head')) {
        return route('login.department-head');
    }

    return route('login.student');
}
```

### RedirectIfAuthenticated Middleware

Redirects already authenticated users to their dashboard:

```php
public function handle(Request $request, Closure $next, string ...$guards): Response
{
    foreach ($guards as $guard) {
        if (Auth::guard($guard)->check()) {
            return match($guard) {
                'admin' => redirect()->route('admin.candidates'),
                'department_head' => redirect()->route('department-head.dashboard'),
                'student' => redirect()->route('dashboard'),
                default => redirect(RouteServiceProvider::HOME),
            };
        }
    }

    return $next($request);
}
```

## Using Guards in Controllers

### Accessing the Authenticated User

**Option 1: Specific Guard**
```php
// In student-only controller
$user = Auth::guard('student')->user();
$userId = Auth::guard('student')->id();
```

**Option 2: Helper Method (for multi-role controllers)**
```php
private function getAuthenticatedUser()
{
    foreach (['admin', 'department_head', 'student', 'web'] as $guard) {
        if (Auth::guard($guard)->check()) {
            return Auth::guard($guard)->user();
        }
    }
    
    abort(401, 'Unauthenticated');
}
```

### Example: VotingController
```php
class VotingController extends Controller
{
    public function index(Request $request)
    {
        // Use student guard explicitly
        $user = Auth::guard('student')->user();
        
        // ... rest of logic
    }
}
```

### Example: DashboardController
```php
class DashboardController extends Controller
{
    public function index()
    {
        // Support multiple guards
        $user = $this->getAuthenticatedUser();
        
        if ($user->role === 'admin') {
            return $this->adminDashboard();
        }
        
        // ... rest of logic
    }
}
```

## Logout Implementation

The logout method now checks all guards:

```php
public function logout(Request $request)
{
    $sessionId = $request->session()->get('user_session_id');

    if ($sessionId) {
        UserSession::where('session_id', $sessionId)
            ->update(['is_active' => false]);
    }

    // Logout from all guards
    $guards = ['admin', 'department_head', 'student', 'web'];
    foreach ($guards as $guard) {
        if (Auth::guard($guard)->check()) {
            Auth::guard($guard)->logout();
            break;
        }
    }

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login.student');
}
```

## Benefits

1. **Security Isolation**: Each user type has separate authentication contexts
2. **Concurrent Sessions**: Different roles can be logged in simultaneously in different tabs
3. **Clear Separation**: Routes and controllers clearly specify which guard they use
4. **Flexible Authorization**: Easy to extend with additional user types
5. **Better Testing**: Can test authentication for specific roles independently

## Testing Guards

### Testing Login
```php
// Test admin login
$admin = User::factory()->create(['role' => 'admin']);
$this->actingAs($admin, 'admin')
    ->get('/admin/candidates')
    ->assertOk();

// Test student login
$student = User::factory()->create(['role' => 'student']);
$this->actingAs($student, 'student')
    ->get('/dashboard')
    ->assertOk();
```

### Testing Unauthorized Access
```php
// Student trying to access admin routes
$student = User::factory()->create(['role' => 'student']);
$this->actingAs($student, 'student')
    ->get('/admin/candidates')
    ->assertRedirect(route('login.admin'));
```

## Login Pages

Each guard has its own login page:

- **Admin**: `/login/admin` → `resources/views/auth/login-admin.blade.php`
- **Department Head**: `/login/department-head` → `resources/views/auth/login-department-head.blade.php`
- **Student**: `/login/student` → `resources/views/auth/login-student.blade.php`

## Session Management

The application maintains session tracking for security:

1. Each login creates a `UserSession` record
2. Browser fingerprinting prevents session hijacking
3. Single-browser restriction (existing sessions invalidated on new login)
4. Session validation middleware checks session validity

## Migration from Single Guard

The application was migrated from a single `web` guard to multiple guards:

### Before
```php
Route::middleware(['auth'])->group(function () {
    // All routes
});

$user = auth()->user();
```

### After
```php
Route::middleware(['auth:student'])->group(function () {
    // Student routes
});

Route::middleware(['auth:admin'])->group(function () {
    // Admin routes
});

$user = Auth::guard('student')->user();
```

## Troubleshooting

### "Unauthenticated" errors
- Ensure the route uses the correct guard: `auth:student`, `auth:admin`, etc.
- Check that the user is logging in through the correct login page

### "Unauthorized access" errors
- Verify the user's role matches the guard being used
- Check middleware is applied correctly to routes

### Session issues
- Clear browser cookies and cache
- Run `php artisan session:clear` (if available)
- Check `config/session.php` settings

## Future Enhancements

Potential improvements:

1. **API Guards**: Add token-based guards for API authentication
2. **Multi-Guard Blade Directives**: Custom directives like `@guardAuth('admin')`
3. **Guard Switching**: Allow admins to impersonate other user types
4. **Guard-Specific Permissions**: Add permission layers within each guard
5. **Remember Me**: Implement separate "remember me" for each guard

## References

- [Laravel Authentication Documentation](https://laravel.com/docs/authentication)
- [Laravel Guards Documentation](https://laravel.com/docs/authentication#adding-custom-guards)
- [Multi-Auth Tutorial](https://laravel.com/docs/authentication#authenticating-users)
