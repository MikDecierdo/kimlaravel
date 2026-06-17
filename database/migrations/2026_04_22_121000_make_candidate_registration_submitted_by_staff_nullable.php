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
        Schema::table('candidate_registrations', function (Blueprint $table) {
            $table->dropForeign(['submitted_by_staff_id']);
            $table->unsignedBigInteger('submitted_by_staff_id')->nullable()->change();
            $table->foreign('submitted_by_staff_id')->references('id')->on('staff')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidate_registrations', function (Blueprint $table) {
            $table->dropForeign(['submitted_by_staff_id']);
            $table->unsignedBigInteger('submitted_by_staff_id')->nullable(false)->change();
            $table->foreign('submitted_by_staff_id')->references('id')->on('staff')->cascadeOnDelete();
        });
    }
};
