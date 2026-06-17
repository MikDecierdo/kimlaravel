<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class UserTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Migrating existing admin and department_head users...');
        
        // Migrate existing Admin users (don't create new accounts)
        $adminUsers = User::where('role', 'admin')
            ->whereDoesntHave('admin')
            ->get();
        
        foreach ($adminUsers as $adminUser) {
            Admin::create([
                'user_id' => $adminUser->id,
                'admin_level' => 'super',
                'permissions' => ['all'],
            ]);
            $this->command->info("  ✓ Created admin profile for: {$adminUser->email}");
        }

        // Migrate existing Staff and Department Head users (don't create new accounts)
        $staffUsers = User::whereIn('role', ['staff', 'department_head'])
            ->whereDoesntHave('staff')
            ->get();
        
        $empCounter = 1;
        foreach ($staffUsers as $staffUser) {
            Staff::create([
                'user_id' => $staffUser->id,
                'employee_id' => 'EMP-' . str_pad($empCounter, 4, '0', STR_PAD_LEFT),
                'department' => $staffUser->department ?? 'General',
                'position' => $staffUser->role === 'department_head' ? 'Department Head' : 'Staff',
                'office_location' => null,
                'phone_number' => null,
                'hire_date' => $staffUser->created_at ?? now(),
            ]);
            $this->command->info("  ✓ Created staff profile for: {$staffUser->email}");
            $empCounter++;
        }

        // Migrate existing Student users (don't create new accounts)
        $this->command->info('Migrating existing student users...');
        $studentUsers = User::where('role', 'student')
            ->whereDoesntHave('student')
            ->get();
        
        foreach ($studentUsers as $studentUser) {
            Student::create([
                'user_id' => $studentUser->id,
                'student_id' => $studentUser->student_id ?? 'STU-' . str_pad($studentUser->id, 6, '0', STR_PAD_LEFT),
                'department' => $studentUser->department ?? 'Undeclared',
                'year_level' => $studentUser->year_level ?? '1st Year',
                'program' => null,
                'section' => null,
                'enrollment_date' => $studentUser->created_at ?? now(),
                'status' => 'active',
            ]);
            $this->command->info("  ✓ Created student profile for: {$studentUser->email}");
        }

        $this->command->info('');
        $this->command->info('User types seeded successfully!');
        $this->command->info("Admins: {$adminUsers->count()}");
        $this->command->info("Staff/Dept Heads: {$staffUsers->count()}");
        $this->command->info("Students: {$studentUsers->count()}");
    }
}
