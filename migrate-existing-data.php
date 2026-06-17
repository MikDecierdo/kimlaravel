<?php

/**
 * Script to migrate existing users to the separated tables
 * Run this after migrating the database if you have existing user data
 * 
 * Usage: php migrate-existing-data.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Admin;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

echo "==========================================\n";
echo "Migrating Existing Users to Separated Tables\n";
echo "==========================================\n\n";

try {
    DB::beginTransaction();
    
    // Migrate Admins
    echo "[1/3] Migrating admins...\n";
    $admins = User::where('role', 'admin')
        ->whereDoesntHave('admin')
        ->get();
    
    $adminCount = 0;
    foreach ($admins as $user) {
        Admin::create([
            'user_id' => $user->id,
            'admin_level' => 'general',
            'permissions' => [],
            'assigned_at' => now(),
        ]);
        $adminCount++;
    }
    echo "   ✓ Migrated {$adminCount} admins\n\n";
    
    // Migrate Staff and Department Heads
    echo "[2/3] Migrating staff and department heads...\n";
    $staffUsers = User::whereIn('role', ['staff', 'department_head'])
        ->whereDoesntHave('staff')
        ->get();
    
    $staffCount = 0;
    foreach ($staffUsers as $user) {
        Staff::create([
            'user_id' => $user->id,
            'employee_id' => 'EMP-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
            'department' => $user->department ?? 'General',
            'position' => $user->role === 'department_head' ? 'Department Head' : 'Staff',
            'office_location' => null,
            'phone_number' => null,
            'hire_date' => $user->created_at,
        ]);
        $staffCount++;
    }
    echo "   ✓ Migrated {$staffCount} staff/department heads\n\n";
    
    // Migrate Students
    echo "[3/3] Migrating students...\n";
    $students = User::where('role', 'student')
        ->whereDoesntHave('student')
        ->get();
    
    $studentCount = 0;
    foreach ($students as $user) {
        Student::create([
            'user_id' => $user->id,
            'student_id' => $user->student_id ?? 'STU-' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
            'department' => $user->department ?? 'Undeclared',
            'year_level' => $user->year_level ?? '1st Year',
            'program' => null,
            'section' => null,
            'enrollment_date' => $user->created_at,
            'status' => 'active',
        ]);
        $studentCount++;
    }
    echo "   ✓ Migrated {$studentCount} students\n\n";
    
    DB::commit();
    
    echo "==========================================\n";
    echo "Migration Summary:\n";
    echo "==========================================\n";
    echo "Admins:              {$adminCount}\n";
    echo "Staff/Dept Heads:    {$staffCount}\n";
    echo "Students:            {$studentCount}\n";
    echo "Total:               " . ($adminCount + $staffCount + $studentCount) . "\n";
    echo "==========================================\n";
    echo "\n✓ Migration completed successfully!\n\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Migration rolled back. No changes were made.\n\n";
    exit(1);
}
