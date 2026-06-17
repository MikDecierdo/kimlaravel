<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Staff;
use App\Models\User;
use App\Models\Candidate;
use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin account in standalone admins table
        $admin = Admin::create([
            'name' => 'Admin',
            'middle_name' => 'none',
            'last_name' => 'User',
            'email' => 'admin@spc.edu',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
            'profile_picture' => null,
            'admin_level' => 'super',
            'permissions' => ['all'],
            'assigned_at' => now(),
        ]);

        // Create Department Head accounts in standalone staff table
        $itHead = Staff::create([
            'name' => 'Carlos',
            'middle_name' => 'M.',
            'last_name' => 'Rivera',
            'email' => 'head.it@spc.edu',
            'email_verified_at' => now(),
            'password' => Hash::make('head123'),
            'profile_picture' => null,
            'employee_id' => 'EMP-0001',
            'department' => 'IT',
            'position' => 'Department Head',
            'office_location' => 'none',
            'phone_number' => 'none',
            'hire_date' => now(),
        ]);

        $bsbaHead = Staff::create([
            'name' => 'Anna',
            'middle_name' => 'L.',
            'last_name' => 'Gomez',
            'email' => 'head.bsba@spc.edu',
            'email_verified_at' => now(),
            'password' => Hash::make('head123'),
            'profile_picture' => null,
            'employee_id' => 'EMP-0002',
            'department' => 'BSBA',
            'position' => 'Department Head',
            'office_location' => 'none',
            'phone_number' => 'none',
            'hire_date' => now(),
        ]);

        // Create Student Users
        User::create([
            'name' => 'Juan',
            'middle_name' => 'D.',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@spc.edu',
            'password' => Hash::make('student123'),
            'role' => 'student',
            'department' => 'IT',
            'student_id' => '2024-00001',
        ]);

        User::create([
            'name' => 'Maria',
            'middle_name' => 'S.',
            'last_name' => 'Santos',
            'email' => 'maria@spc.edu',
            'password' => Hash::make('student123'),
            'role' => 'student',
            'department' => 'BSBA',
            'student_id' => '2024-00002',
        ]);

        // Create Candidates
        Candidate::create([
            'first_name' => 'Juan',
            'middle_name' => 'D.',
            'last_name' => 'Dela Cruz',
            'student_id' => '2024-00001',
            'position' => 'President',
            'department' => 'IT',
            'description' => 'Experienced leader with vision for student development',
            'image' => 'https://picsum.photos/seed/juan/300/200',
            'votes' => 120,
        ]);

        Candidate::create([
            'first_name' => 'Maria',
            'middle_name' => 'S.',
            'last_name' => 'Santos',
            'student_id' => '2024-00002',
            'position' => 'President',
            'department' => 'BSBA',
            'description' => 'Passionate about business and student welfare',
            'image' => 'https://picsum.photos/seed/maria/300/200',
            'votes' => 95,
        ]);

        Candidate::create([
            'first_name' => 'Pedro',
            'middle_name' => 'A.',
            'last_name' => 'Reyes',
            'student_id' => '2024-00003',
            'position' => 'Vice President',
            'department' => 'IT',
            'description' => 'Dedicated to serving the student body',
            'image' => 'https://picsum.photos/seed/pedro/300/200',
            'votes' => 80,
        ]);

        Candidate::create([
            'first_name' => 'Ana',
            'middle_name' => 'B.',
            'last_name' => 'Garcia',
            'student_id' => '2024-00004',
            'position' => 'Secretary',
            'department' => 'EDUC',
            'description' => 'Organized and detail-oriented leader',
            'image' => 'https://picsum.photos/seed/ana/300/200',
            'votes' => 150,
        ]);

        Candidate::create([
            'first_name' => 'Luis',
            'middle_name' => 'C.',
            'last_name' => 'Cruz',
            'student_id' => '2024-00005',
            'position' => 'Representative',
            'department' => 'CRIM',
            'description' => 'Voice of the criminology students',
            'image' => 'https://picsum.photos/seed/luis/300/200',
            'votes' => 60,
        ]);

        Candidate::create([
            'first_name' => 'Sofia',
            'middle_name' => 'D.',
            'last_name' => 'Rodriguez',
            'student_id' => '2024-00006',
            'position' => 'Representative',
            'department' => 'ENGINEERING',
            'description' => 'Engineering excellence and student advocacy',
            'image' => 'https://picsum.photos/seed/sofia/300/200',
            'votes' => 45,
        ]);

        // Create Events

        Event::create([
            'title' => 'IT Hackathon 2024',
            'department' => 'IT',
            'event_date' => now()->addDays(15),
            'description' => 'Join us for a 24-hour coding marathon! Showcase your programming skills and compete for amazing prizes.',
            'staff_id' => $itHead->id,
        ]);

        Event::create([
            'title' => 'Business Ethics Seminar',
            'department' => 'BSBA',
            'event_date' => now()->addDays(20),
            'description' => 'Guest speakers from top corporations discuss modern ethics in business practices.',
            'staff_id' => $bsbaHead->id,
        ]);

        Event::create([
            'title' => 'Crime Prevention Week',
            'department' => 'CRIM',
            'event_date' => now()->addDays(22),
            'description' => 'Awareness drive and safety demonstrations for the entire campus community.',
            'staff_id' => $itHead->id,
        ]);

        Event::create([
            'title' => 'Teaching Strategies Workshop',
            'department' => 'EDUC',
            'event_date' => now()->addDays(25),
            'description' => 'Modern techniques and innovative approaches for future educators.',
            'staff_id' => $bsbaHead->id,
        ]);
    }
}
