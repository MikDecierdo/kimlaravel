<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * For each table, list the varchar/text data columns that should
     * store 'none' instead of NULL when no value is provided.
     * System columns (timestamps, remember_token, file paths, FKs) are left as nullable.
     */
    private array $targets = [
        'admins' => [
            'middle_name',
            'last_name',
        ],
        'staff' => [
            'middle_name',
            'last_name',
            'position',
            'office_location',
            'phone_number',
        ],
        'students' => [
            'year_level',
            'program',
            'section',
        ],
        'users' => [
            'middle_name',
            'last_name',
            'department',
            'year_level',
            'student_id',
        ],
        'candidates' => [
            'middle_name',
            'description',
            'advocacy',
        ],
        'campus_elections' => [
            'description',
        ],
    ];

    public function up(): void
    {
        foreach ($this->targets as $table => $columns) {
            // Step 1: replace existing NULLs with 'none'
            foreach ($columns as $col) {
                DB::table($table)->whereNull($col)->update([$col => 'none']);
            }

            // Step 2: alter column to NOT NULL with default 'none'
            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                foreach ($columns as $col) {
                    $blueprint->string($col)->default('none')->nullable(false)->change();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->targets as $table => $columns) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                foreach ($columns as $col) {
                    // Revert to nullable with no default
                    $blueprint->string($col)->nullable()->default(null)->change();
                }
            });

            // Revert 'none' back to NULL on rollback
            foreach ($columns as $col) {
                DB::table($table)->where($col, 'none')->update([$col => null]);
            }
        }
    }
};
