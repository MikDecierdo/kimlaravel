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
        Schema::table('event_likes', function (Blueprint $table) {
            $table->string('reaction_type')->default('like')->after('user_id'); // like, haha, love, etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_likes', function (Blueprint $table) {
            $table->dropColumn('reaction_type');
        });
    }
};
