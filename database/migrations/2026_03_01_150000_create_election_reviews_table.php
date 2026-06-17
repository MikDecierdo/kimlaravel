<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('election_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('campus_election_id')->constrained('campus_elections')->onDelete('cascade');
            $table->tinyInteger('rating')->unsigned(); // 1–5
            $table->text('review')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'campus_election_id']); // one review per student per election
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_reviews');
    }
};
