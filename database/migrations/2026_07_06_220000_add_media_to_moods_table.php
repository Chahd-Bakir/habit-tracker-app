<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moods', function (Blueprint $table) {
            $table->string('photo_path')->nullable();
            $table->string('voice_note_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('moods', function (Blueprint $table) {
            $table->dropColumn(['photo_path', 'voice_note_path']);
        });
    }
};
