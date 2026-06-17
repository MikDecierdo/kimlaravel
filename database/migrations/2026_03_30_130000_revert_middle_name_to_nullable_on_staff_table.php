<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Reverts middle_name to nullable on staff table to allow accounts without middle names
     */
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->string('middle_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->string('middle_name')->default('none')->nullable(false)->change();
        });
    }
};
