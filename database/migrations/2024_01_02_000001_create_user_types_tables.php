<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create admins table
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('admin_level')->default('general'); // general, super, etc.
            $table->json('permissions')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
            
            $table->unique('user_id');
        });

        // Create staff table
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('employee_id')->unique();
            $table->string('department');
            $table->string('position')->nullable();
            $table->string('office_location')->nullable();
            $table->string('phone_number')->nullable();
            $table->date('hire_date')->nullable();
            $table->timestamps();
            
            $table->unique('user_id');
        });

        // Create students table
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('student_id')->unique();
            $table->string('department');
            $table->string('year_level')->nullable();
            $table->string('program')->nullable();
            $table->string('section')->nullable();
            $table->date('enrollment_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'graduated', 'suspended'])->default('active');
            $table->timestamps();
            
            $table->unique('user_id');
        });

        // Add index to users.role for better query performance
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('admins');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
        });
    }
};
