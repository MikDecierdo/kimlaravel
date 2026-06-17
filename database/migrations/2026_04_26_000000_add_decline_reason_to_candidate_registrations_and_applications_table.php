<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_registrations', function (Blueprint $table) {
            $table->text('decline_reason')->nullable()->after('description');
        });

        Schema::table('candidate_applications', function (Blueprint $table) {
            $table->text('decision_description')->nullable()->after('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('candidate_registrations', function (Blueprint $table) {
            $table->dropColumn('decline_reason');
        });

        Schema::table('candidate_applications', function (Blueprint $table) {
            $table->dropColumn('decision_description');
        });
    }
};