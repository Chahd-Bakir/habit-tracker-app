<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habit extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'frequency',
        'icon',
        'color',
        'custom_days',
        'reminder_time',
        'status',
    ];

    protected $casts = [
        'custom_days' => 'array',
        'status' => 'string',
    ];

    protected $appends = ['completed_today'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(HabitLog::class);
    }

    public function getCompletedTodayAttribute(): bool
    {
        return $this->logs()
            ->whereDate('completed_date', now()->toDateString())
            ->exists();
    }
}
