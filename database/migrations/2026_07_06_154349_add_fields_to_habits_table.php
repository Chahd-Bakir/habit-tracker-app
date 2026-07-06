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
        Schema::table('habits', function (Blueprint $table) {
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->json('custom_days')->nullable();
            $table->time('reminder_time')->nullable();
        });

        Schema::table('habits', function (Blueprint $table) {
            $table->enum('frequency', ['daily', 'weekly', 'custom'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('habits', function (Blueprint $table) {
            $table->dropColumn(['icon', 'color', 'custom_days', 'reminder_time']);
        });

        Schema::table('habits', function (Blueprint $table) {
            $table->enum('frequency', ['daily', 'weekly'])->change();
        });
    }
};
