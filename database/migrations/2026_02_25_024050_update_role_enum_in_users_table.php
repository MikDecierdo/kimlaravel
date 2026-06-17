<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remove admin and department_head from the role enum since those accounts
     * now live in their own standalone tables (admins, staff).
     */
    public function up(): void
    {
        // MySQL requires raw SQL to modify ENUM values
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('student') NOT NULL DEFAULT 'student'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','staff','student','department_head') NOT NULL DEFAULT 'student'");
    }
};
