<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyPrompt extends Model
{
    protected $fillable = ['content', 'date', 'source'];

    protected $casts = [
        'date' => 'date',
    ];
}
