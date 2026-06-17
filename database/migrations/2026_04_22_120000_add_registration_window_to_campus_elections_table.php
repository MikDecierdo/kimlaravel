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
        Schema::table('campus_elections', function (Blueprint $table) {
            $table->date('registration_start_date')->nullable()->after('candidate_registration_schema');
            $table->date('registration_end_date')->nullable()->after('registration_start_date');
        });

        DB::table('campus_elections')->whereNull('registration_start_date')->update([
            'registration_start_date' => DB::raw('start_date'),
        ]);

        DB::table('campus_elections')->whereNull('registration_end_date')->update([
            'registration_end_date' => DB::raw('end_date'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campus_elections', function (Blueprint $table) {
            $table->dropColumn(['registration_start_date', 'registration_end_date']);
        });
    }
};
