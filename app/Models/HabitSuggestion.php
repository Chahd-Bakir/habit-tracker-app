<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HabitSuggestion extends Model
{
    protected $fillable = ['name', 'icon', 'category_id'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(HabitCategory::class, 'category_id');
    }
}
