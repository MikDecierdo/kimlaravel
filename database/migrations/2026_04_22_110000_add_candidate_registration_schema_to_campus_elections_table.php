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
        Schema::table('campus_elections', function (Blueprint $table) {
            $table->json('candidate_registration_schema')->nullable()->after('positions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campus_elections', function (Blueprint $table) {
            $table->dropColumn('candidate_registration_schema');
        });
    }
};
