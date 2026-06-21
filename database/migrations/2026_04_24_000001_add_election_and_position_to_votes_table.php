<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->foreignId('campus_election_id')->nullable()->after('user_id')->constrained('campus_elections')->cascadeOnDelete();
            $table->string('position')->nullable()->after('campus_election_id');
        });

        DB::statement("UPDATE votes SET campus_election_id = candidates.campus_election_id, position = candidates.position FROM candidates WHERE votes.candidate_id = candidates.id");

        DB::statement('DELETE FROM votes v1 USING votes v2 WHERE v1.user_id = v2.user_id AND v1.campus_election_id = v2.campus_election_id AND v1.position = v2.position AND v1.id > v2.id');

        DB::statement('ALTER TABLE votes ALTER COLUMN campus_election_id SET NOT NULL');
        DB::statement('ALTER TABLE votes ALTER COLUMN position SET NOT NULL');
        DB::statement('ALTER TABLE votes ADD CONSTRAINT votes_user_election_position_unique UNIQUE (user_id, campus_election_id, position)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE votes DROP CONSTRAINT votes_user_election_position_unique');
        Schema::table('votes', function (Blueprint $table) {
            $table->dropForeign(['campus_election_id']);
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->dropColumn(['campus_election_id', 'position']);
        });
    }
};