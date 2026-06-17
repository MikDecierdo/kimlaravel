<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campus_elections', function (Blueprint $table) {
            $table->time('voting_start_time')->default('08:00:00')->after('end_date');
            $table->time('voting_end_time')->default('17:00:00')->after('voting_start_time');
        });
    }

    public function down(): void
    {
        Schema::table('campus_elections', function (Blueprint $table) {
            $table->dropColumn(['voting_start_time', 'voting_end_time']);
        });
    }
};