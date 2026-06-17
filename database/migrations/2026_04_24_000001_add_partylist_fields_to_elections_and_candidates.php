<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campus_elections', function (Blueprint $table) {
            $table->json('partylist_teams')->nullable()->after('positions');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->string('partylist')->nullable()->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn('partylist');
        });

        Schema::table('campus_elections', function (Blueprint $table) {
            $table->dropColumn('partylist_teams');
        });
    }
};