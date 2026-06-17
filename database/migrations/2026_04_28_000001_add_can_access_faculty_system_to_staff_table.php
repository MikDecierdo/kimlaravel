<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->boolean('can_access_faculty_system')->default(true)->after('can_access_department_portal');
        });

        DB::table('staff')
            ->where('is_department_head', false)
            ->update(['can_access_faculty_system' => true]);
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn('can_access_faculty_system');
        });
    }
};