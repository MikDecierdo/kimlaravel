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

        DB::table('votes')
            ->join('candidates', 'votes.candidate_id', '=', 'candidates.id')
            ->update([
                'votes.campus_election_id' => DB::raw('candidates.campus_election_id'),
                'votes.position' => DB::raw('candidates.position'),
            ]);

        DB::statement('DELETE v1 FROM votes v1 INNER JOIN votes v2 ON v1.user_id = v2.user_id AND v1.campus_election_id = v2.campus_election_id AND v1.position = v2.position AND v1.id > v2.id');

        DB::statement('ALTER TABLE votes MODIFY campus_election_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE votes MODIFY position VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE votes ADD UNIQUE KEY votes_user_election_position_unique (user_id, campus_election_id, position)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE votes DROP INDEX votes_user_election_position_unique');
        Schema::table('votes', function (Blueprint $table) {
            $table->dropForeign(['campus_election_id']);
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->dropColumn(['campus_election_id', 'position']);
        });
    }
};