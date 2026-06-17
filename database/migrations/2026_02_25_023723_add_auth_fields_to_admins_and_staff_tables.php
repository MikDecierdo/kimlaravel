<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds standalone auth fields to admins and staff tables so they
     * no longer depend on the users table for authentication.
     */
    public function up(): void
    {
        // --- admins table ---
        Schema::table('admins', function (Blueprint $table) {
            // Drop FK and make user_id nullable (kept for historical reference)
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Auth fields
            $table->string('name')->after('id');
            $table->string('middle_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('email')->unique()->after('last_name');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('password')->after('email_verified_at');
            $table->string('profile_picture')->nullable()->after('password');
            $table->rememberToken()->after('profile_picture');
        });

        // --- staff table ---
        Schema::table('staff', function (Blueprint $table) {
            // Drop FK and make user_id nullable (kept for historical reference)
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Auth fields
            $table->string('name')->after('id');
            $table->string('middle_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('email')->unique()->after('last_name');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('password')->after('email_verified_at');
            $table->string('profile_picture')->nullable()->after('password');
            $table->rememberToken()->after('profile_picture');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['name','middle_name','last_name','email','email_verified_at','password','profile_picture','remember_token']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['name','middle_name','last_name','email','email_verified_at','password','profile_picture','remember_token']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
