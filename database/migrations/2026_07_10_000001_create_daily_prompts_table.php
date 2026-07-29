<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_prompts', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->date('date');
            $table->timestamps();

            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_prompts');
    }
};
