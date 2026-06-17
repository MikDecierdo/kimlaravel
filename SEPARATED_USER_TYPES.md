# Separated User Types Implementation

## Overview
The user accounts are now separated into distinct tables while maintaining centralized authentication through the `users` table.

## Database Structure

### Main Tables

1. **users** - Main authentication table
   - Contains login credentials and basic info
   - Fields: id, name, middle_name, last_name, email, password, role, etc.

2. **admins** - Admin-specific data
   - user_id (foreign key to users)
   - admin_level (general, super)
   - permissions (JSON array)
   - assigned_at

3. **staff** - Staff-specific data
   - user_id (foreign key to users)
   - employee_id (unique)
   - department
   - position
   - office_location
   - phone_number
   - hire_date

4. **students** - Student-specific data
   - user_id (foreign key to users)
   - student_id (unique)
   - department
   - year_level
   - program
   - section
   - enrollment_date
   - status (active, inactive, graduated, suspended)

## Models

### User Model
The base `User` model now has relationships to access role-specific data:

```php
$user = User::find(1);

// Access specific profile
$user->admin;    // Returns Admin model or null
$user->staff;    // Returns Staff model or null
$user->student;  // Returns Student model or null

// Get active profile based on role
$profile = $user->profile();

// Check role
$user->isAdmin();
$user->isStaff();
$user->isStudent();
```

### Admin Model
```php
$admin = Admin::with('user')->find(1);
$admin->user; // Get associated User
$admin->hasPermission('manage_elections'); // Check permission
```

### Staff Model
```php
$staff = Staff::with('user')->find(1);
$staff->user; // Get associated User
$staff->isFromDepartment('Computer Science'); // Check department
```

### Student Model
```php
$student = Student::with('user')->find(1);
$student->user; // Get associated User
$student->votes; // Get student's votes
$student->isActive(); // Check if active
$student->isYearLevel('3rd Year'); // Check year level
```

## Creating New Users

### Creating an Admin
```php
// Create user account
$user = User::create([
    'name' => 'Admin Name',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'role' => 'admin',
]);

// Create admin profile
Admin::create([
    'user_id' => $user->id,
    'admin_level' => 'super',
    'permissions' => ['all'],
]);
```

### Creating a Staff Member
```php
// Create user account
$user = User::create([
    'name' => 'Staff Name',
    'email' => 'staff@example.com',
    'password' => Hash::make('password'),
    'role' => 'staff',
    'department' => 'Computer Science',
]);

// Create staff profile
Staff::create([
    'user_id' => $user->id,
    'employee_id' => 'EMP-001',
    'department' => 'Computer Science',
    'position' => 'Coordinator',
    'office_location' => 'Building A',
    'phone_number' => '123-456-7890',
    'hire_date' => now(),
]);
```

### Creating a Student
```php
// Create user account
$user = User::create([
    'name' => 'Student Name',
    'email' => 'student@example.com',
    'password' => Hash::make('password'),
    'role' => 'student',
    'department' => 'Computer Science',
    'student_id' => '2024-0001',
]);

// Create student profile
Student::create([
    'user_id' => $user->id,
    'student_id' => '2024-0001',
    'department' => 'Computer Science',
    'year_level' => '3rd Year',
    'program' => 'BSCS',
    'section' => 'A',
    'enrollment_date' => now(),
    'status' => 'active',
]);
```

## Querying Users by Type

### Get All Admins with User Data
```php
$admins = Admin::with('user')->get();
foreach ($admins as $admin) {
    echo $admin->user->name;
    echo $admin->admin_level;
}
```

### Get All Active Students
```php
$students = Student::where('status', 'active')
    ->with('user')
    ->get();
```

### Get Staff by Department
```php
$csStaff = Staff::where('department', 'Computer Science')
    ->with('user')
    ->get();
```

### Get Users by Role
```php
$admins = User::where('role', 'admin')->with('admin')->get();
$staff = User::where('role', 'staff')->with('staff')->get();
$students = User::where('role', 'student')->with('student')->get();
```

## Authentication Guards

Separate guards are configured for each user type:
- `admin` - For admin users
- `staff` - For staff users
- `department_head` - For department heads (uses staff table)
- `student` - For students

Usage in controllers:
```php
// Check if authenticated as admin
if (auth()->guard('admin')->check()) {
    // Admin is logged in
}

// Get authenticated student
$student = auth()->guard('student')->user();
```

## Migration Steps

1. **Backup your database** (Important!)

2. **Run the migration:**
   ```bash
   php artisan migrate
   ```

3. **Migrate existing user data:**
   ```bash
   php artisan db:seed --class=UserTypesSeeder
   ```
   
   This will automatically create profile records for ALL existing users without creating new accounts.
   Your existing admin and department_head credentials will remain unchanged.

## Manual Migration (Alternative)

If you prefer to migrate data manually, you can create corresponding records in the new tables:

```php
// For existing admins
$admins = User::where('role', 'admin')->get();
foreach ($admins as $user) {
    Admin::create([
        'user_id' => $user->id,
        'admin_level' => 'general',
        'permissions' => [],
    ]);
}

// For existing students
$students = User::where('role', 'student')->get();
foreach ($students as $user) {
    Student::create([
        'user_id' => $user->id,
        'student_id' => $user->student_id ?? 'TEMP-' . $user->id,
        'department' => $user->department ?? 'Undeclared',
        'year_level' => $user->year_level ?? '1st Year',
        'status' => 'active',
    ]);
}

// For existing staff/department heads
$staff = User::whereIn('role', ['staff', 'department_head'])->get();
foreach ($staff as $user) {
    Staff::create([
        'user_id' => $user->id,
        'employee_id' => 'EMP-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
        'department' => $user->department ?? 'General',
        'position' => $user->role === 'department_head' ? 'Department Head' : 'Staff',
    ]);
}
```

## Benefits

1. **Cleaner Data Structure** - Each user type has its own table with relevant fields
2. **Better Performance** - Indexed foreign keys improve query performance
3. **Type Safety** - Specific models for each user type
4. **Easier Maintenance** - Changes to one user type don't affect others
5. **Clear Relationships** - Explicit relationships between users and their profiles

## Notes

- All users still authenticate through the `users` table
- The `role` field in the `users` table determines which profile table to use
- Department heads are stored in the `staff` table with their position field set accordingly
- Deleting a user automatically deletes their profile (cascade delete)
