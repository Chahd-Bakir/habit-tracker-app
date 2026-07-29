<?php

namespace Database\Seeders;

use App\Models\HabitCategory;
use App\Models\HabitSuggestion;
use Illuminate\Database\Seeder;

class HabitSuggestionSeeder extends Seeder
{
    public function run(): void
    {
        $health = HabitCategory::where('name', 'Health')->first();
        $calm = HabitCategory::where('name', 'Calm')->first();

        HabitSuggestion::firstOrCreate(
            ['name' => 'Drink 8 glasses of water'],
            ['icon' => "\u{1F4A7}", 'category_id' => $health?->id]
        );

        HabitSuggestion::firstOrCreate(
            ['name' => '5-minute meditation'],
            ['icon' => "\u{1F9D8}\u{200D}\u{2640}\u{FE0F}", 'category_id' => $calm?->id]
        );
    }
}
