<?php

namespace Database\Seeders;

use App\Models\HabitCategory;
use Illuminate\Database\Seeder;

class HabitCategorySeeder extends Seeder
{
    public function run(): void
    {
        HabitCategory::firstOrCreate(['name' => 'Health']);
        HabitCategory::firstOrCreate(['name' => 'Calm']);
    }
}
