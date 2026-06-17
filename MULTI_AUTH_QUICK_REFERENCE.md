# Multiple Authentication Guards - Quick Reference

## Login URLs

| Role | Login URL | After Login Redirect |
|------|-----------|---------------------|
| Student | `/login/student` | `/dashboard` |
| Admin | `/login/admin` | `/admin/candidates` |
| Department Head | `/login/department-head` | `/department-head/dashboard` |

## Guard Names

- `student` - Student guard
- `admin` - Admin guard
- `department_head` - Department Head guard
- `web` - Fallback/default guard

## Route Protection Syntax

```php
// Single guard
Route::middleware(['auth:student'])->group(function () {
    // Student-only routes
});

// Multiple guards (user must be authenticated in ANY of these)
Route::middleware(['auth:student,admin'])->group(function () {
    // Routes accessible by students OR admins
});
```

## Controller Authentication

```php
use Illuminate\Support\Facades\Auth;

// Get authenticated user from specific guard
$user = Auth::guard('student')->user();
$userId = Auth::guard('student')->id();

// Check if authenticated
if (Auth::guard('admin')->check()) {
    // User is authenticated via admin guard
}

// Login user
Auth::guard('student')->login($user);

// Logout user
Auth::guard('student')->logout();
```

## Middleware Usage

### In Routes (web.php)
```php
// Admin routes
Route::middleware(['auth:admin', 'admin'])->prefix('admin')->group(function () {
    Route::get('/candidates', [AdminController::class, 'candidates']);
});

// Student routes
Route::middleware(['auth:student', 'student'])->group(function () {
    Route::get('/voting', [VotingController::class, 'index']);
});
```

### Middleware Aliases (from Http/Kernel.php)
```php
'auth' => Authenticate::class,              // Base auth middleware
'admin' => AdminMiddleware::class,          // Role verification
'student' => StudentMiddleware::class,      // Role verification
'department_head' => DepartmentHead::class, // Role verification
```

## Blade Directives

```blade
{{-- Check specific guard --}}
@if(Auth::guard('admin')->check())
    <p>Logged in as Admin</p>
@endif

@if(Auth::guard('student')->check())
    <p>Logged in as Student</p>
@endif

{{-- Get authenticated user from guard --}}
<p>Welcome, {{ Auth::guard('student')->user()->name }}</p>

{{-- Check any authentication (checks all guards) --}}
@auth
    <p>Logged in</p>
@endauth

@guest
    <p>Not logged in</p>
@endguest
```

## Common Patterns

### Pattern 1: Student-Only Controller
```php
class VotingController extends Controller
{
    public function index()
    {
        $user = Auth::guard('student')->user();
        // ... student-specific logic
    }
}
```

### Pattern 2: Multi-Role Controller
```php
class DashboardController extends Controller
{
    public function index()
    {
        $user = $this->getCurrentUser();
        
        return match($user->role) {
            'admin' => $this->adminDashboard(),
            'department_head' => redirect()->route('department-head.dashboard'),
            'student' => $this->studentDashboard(),
        };
    }
    
    private function getCurrentUser()
    {
        foreach (['admin', 'department_head', 'student'] as $guard) {
            if (Auth::guard($guard)->check()) {
                return Auth::guard($guard)->user();
            }
        }
        abort(401);
    }
}
```

### Pattern 3: API Routes with Guards
```php
Route::middleware(['auth:sanctum,admin'])->group(function () {
    Route::get('/api/admin/stats', [ApiController::class, 'adminStats']);
});
```

## Testing

```php
use Tests\TestCase;
use App\Models\User;

class AuthenticationTest extends TestCase
{
    /** @test */
    public function admin_can_access_admin_routes()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $this->actingAs($admin, 'admin')
            ->get('/admin/candidates')
            ->assertOk();
    }
    
    /** @test */
    public function student_cannot_access_admin_routes()
    {
        $student = User::factory()->create(['role' => 'student']);
        
        $this->actingAs($student, 'student')
            ->get('/admin/candidates')
            ->assertRedirect(route('login.admin'));
    }
}
```

## Common Errors & Solutions

### Error: "Unauthenticated"
**Cause**: Route doesn't specify correct guard
**Solution**: Add guard to middleware: `auth:student`

### Error: "Unauthorized access"
**Cause**: User logged in with wrong guard for the route
**Solution**: Ensure user logs in via correct login page

### Error: Session not persisting
**Cause**: Guard mismatch between login and route
**Solution**: Verify LoginController uses correct guard and routes use matching guard

## File Locations

| File | Purpose |
|------|---------|
| `config/auth.php` | Guard and provider configuration |
| `app/Http/Middleware/AdminMiddleware.php` | Admin role verification |
| `app/Http/Middleware/StudentMiddleware.php` | Student role verification |
| `app/Http/Middleware/DepartmentHead.php` | Dept head role verification |
| `app/Http/Middleware/Authenticate.php` | Guard-aware redirects |
| `app/Http/Controllers/Auth/LoginController.php` | Multi-guard login logic |
| `routes/web.php` | Guard-protected routes |

## Quick Commands

```bash
# Clear all authentication sessions
php artisan cache:clear
php artisan session:clear

# View routes with middleware
php artisan route:list --columns=uri,name,middleware

# Test specific guard
php artisan tinker
>>> Auth::guard('student')->check()
>>> Auth::guard('admin')->user()
```
