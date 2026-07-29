<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Habit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'frequency',
        'icon',
        'color',
        'custom_days',
        'reminder_time',
        'status',
        'position',
    ];

    protected $casts = [
        'custom_days' => 'array',
        'status' => 'string',
        'position' => 'integer',
        'current_streak' => 'integer',
        'longest_streak' => 'integer',
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

    public function recalculateStreak(): bool
    {
        $today = now()->startOfDay();
        $logs = $this->logs()
            ->where('completed_date', '<=', $today)
            ->orderBy('completed_date', 'desc')
            ->get();

        $currentStreak = 0;

        if ($this->frequency === 'weekly') {
            $logsByWeekStart = $logs->groupBy(
                fn($log) => $log->completed_date instanceof Carbon
                    ? $log->completed_date->copy()->startOfWeek()->toDateString()
                    : Carbon::parse($log->completed_date)->startOfWeek()->toDateString()
            );

            $weekStart = $today->copy()->startOfWeek();
            if (!$logsByWeekStart->has($weekStart->toDateString())) {
                $weekStart->subWeek();
            }
            for ($i = 0; $i < 52; $i++) {
                if ($logsByWeekStart->has($weekStart->toDateString())) {
                    $currentStreak++;
                    $weekStart->subWeek();
                } else {
                    break;
                }
            }
        } else {
            $logDates = $logs->pluck('completed_date')
                ->map(fn($d) => $d instanceof Carbon ? $d->toDateString() : (string) $d)
                ->toArray();

            $date = $today->copy();
            if (!in_array($date->toDateString(), $logDates)) {
                $date->subDay();
            }
            for ($i = 0; $i < 365; $i++) {
                $isScheduled = $this->frequency === 'daily'
                    || ($this->frequency === 'custom'
                        && in_array(strtolower($date->format('l')), $this->custom_days ?? []));

                if ($isScheduled) {
                    if (in_array($date->toDateString(), $logDates)) {
                        $currentStreak++;
                    } else {
                        break;
                    }
                }
                $date->subDay();
            }
        }

        $changed = $this->current_streak !== $currentStreak;
        $longest = max($currentStreak, $this->longest_streak ?? 0);

        if ($longest !== ($this->longest_streak ?? 0)) {
            $changed = true;
        }

        if ($changed) {
            $this->current_streak = $currentStreak;
            $this->longest_streak = $longest;
            $this->save();
        }

        return $changed;
    }
}
