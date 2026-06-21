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
        Schema::table('candidate_registrations', function (Blueprint $table) {
            $table->dropForeign(['submitted_by_staff_id']);
        });

        DB::statement('ALTER TABLE candidate_registrations ALTER COLUMN submitted_by_staff_id DROP NOT NULL');
        DB::statement('ALTER TABLE candidate_registrations ADD CONSTRAINT candidate_registrations_submitted_by_staff_id_foreign FOREIGN KEY (submitted_by_staff_id) REFERENCES staff(id) ON DELETE SET NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidate_registrations', function (Blueprint $table) {
            $table->dropForeign(['submitted_by_staff_id']);
        });

        DB::statement('ALTER TABLE candidate_registrations ALTER COLUMN submitted_by_staff_id SET NOT NULL');
        DB::statement('ALTER TABLE candidate_registrations ADD CONSTRAINT candidate_registrations_submitted_by_staff_id_foreign FOREIGN KEY (submitted_by_staff_id) REFERENCES staff(id) ON DELETE CASCADE');
    }
};