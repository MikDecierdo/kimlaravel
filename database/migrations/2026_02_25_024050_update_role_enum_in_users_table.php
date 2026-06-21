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
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(50) USING role::VARCHAR(50)");
        DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'student'");
        DB::statement("ALTER TABLE users ALTER COLUMN role SET NOT NULL");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('student'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin','staff','student','department_head'))");
    }
};
