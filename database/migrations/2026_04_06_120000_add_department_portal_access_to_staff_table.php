<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->boolean('can_access_department_portal')->default(false)->after('is_department_head');
            $table->json('department_portal_permissions')->nullable()->after('can_access_department_portal');
        });

        DB::table('staff')
            ->where('is_department_head', true)
            ->update([
                'can_access_department_portal' => true,
                'department_portal_permissions' => json_encode([
                    'create_election',
                    'add_candidates',
                    'post_events',
                    'approve_students',
                ]),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['can_access_department_portal', 'department_portal_permissions']);
        });
    }
};
