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
        Schema::create('candidate_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_election_id')->constrained('campus_elections')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('position');
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('submitted_by_staff_id')->constrained('staff')->onDelete('cascade');
            $table->foreignId('reviewed_by_staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['campus_election_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_registrations');
    }
};
