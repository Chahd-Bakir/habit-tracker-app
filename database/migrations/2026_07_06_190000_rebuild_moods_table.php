<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('moods');

        Schema::create('moods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('mood');
            $table->text('note')->nullable();
            $table->date('date');
            $table->unique(['user_id', 'date']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moods');
    }
};
