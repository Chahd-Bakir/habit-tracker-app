<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('reminder_enabled')->default(true)->after('onboarding_completed');
            $table->time('reminder_time')->default('20:00:00')->after('reminder_enabled');
            $table->string('timezone', 64)->default('Africa/Tunis')->after('reminder_time');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reminder_enabled', 'reminder_time', 'timezone']);
        });
    }
};
