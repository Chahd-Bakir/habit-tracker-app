<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HabitLog;
use App\Models\Mood;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private const MOOD_LABELS = [1 => 'Very sad', 2 => 'Sad', 3 => 'Neutral', 4 => 'Happy', 5 => 'Very happy'];

    private const MOOD_EMOJIS = [1 => '😫', 2 => '😢', 3 => '😐', 4 => '🙂', 5 => '😄'];

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = now()->startOfDay();

        $habits = $user->habits()->orderBy('position')->get();

        $completedToday = $user->habits()
            ->join('habit_logs', 'habits.id', '=', 'habit_logs.habit_id')
            ->whereDate('habit_logs.completed_date', $today)
            ->pluck('habit_logs.habit_id')
            ->all();

        $totalToday = 0;
        $doneToday = 0;

        foreach ($habits as $habit) {
            if ($habit->created_at->copy()->startOfDay()->gt($today)) {
                continue;
            }
            if ($habit->deleted_at && Carbon::parse($habit->deleted_at)->startOfDay()->lt($today)) {
                continue;
            }

            $scheduled = match ($habit->frequency) {
                'daily' => true,
                'weekly' => true,
                'custom' => in_array(strtolower($today->format('l')), $habit->custom_days ?? []),
                default => false,
            };

            if (! $scheduled) {
                continue;
            }

            $totalToday++;
            if (in_array($habit->id, $completedToday)) {
                $doneToday++;
            }
        }

        $percentage = $totalToday > 0 ? round(($doneToday / $totalToday) * 100) : 0;

        $currentStreak = (int) $habits->filter(fn ($h) => $h->current_streak !== null)->max('current_streak');
        $longestStreak = (int) $habits->filter(fn ($h) => $h->longest_streak !== null)->max('longest_streak');

        $recentMoods = $user->moods()
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(7)
            ->get();

        $moodScore = null;
        $moodLabel = null;

        if ($recentMoods->count() > 0) {
            $avg = round((float) $recentMoods->avg('mood'), 1);
            $moodScore = round($avg * 2, 1);
            $moodLabel = match (true) {
                $avg >= 4.5 => 'Feeling great',
                $avg >= 3.5 => 'Good energy',
                $avg >= 2.5 => 'Steady and calm',
                $avg >= 1.5 => 'A bit low',
                default => 'Really tough',
            };
        }

        $recentActivity = $this->recentActivity($user, $habits->pluck('id'));

        return response()->json([
            'success' => true,
            'daily_progress' => [
                'completed' => $doneToday,
                'total' => $totalToday,
                'percentage' => $percentage,
            ],
            'streak' => [
                'current' => $currentStreak,
                'longest' => $longestStreak,
            ],
            'mood' => [
                'score' => $moodScore,
                'label' => $moodLabel,
            ],
            'recent_activity' => $recentActivity,
        ]);
    }

    private function recentActivity($user, $habitIds): array
    {
        $items = [];

        if ($habitIds->isNotEmpty()) {
            $logs = HabitLog::whereIn('habit_id', $habitIds)
                ->where('created_at', '>=', now()->subDays(7))
                ->with('habit:id,title,icon')
                ->orderByDesc('created_at')
                ->limit(8)
                ->get();

            foreach ($logs as $log) {
                $items[] = [
                    'at' => $log->created_at?->toIso8601String() ?? now()->toIso8601String(),
                    'type' => 'habit',
                    'title' => $log->habit?->title ?? 'Completed a habit',
                    'description' => 'Habit completed',
                    'time' => $log->created_at?->format('H:i') ?? '',
                    'icon' => $log->habit?->icon ?? '✅',
                ];
            }
        }

        $moods = Mood::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->orderByDesc('created_at')
            ->limit(8)
            ->get(['id', 'mood', 'created_at']);

        foreach ($moods as $mood) {
            $items[] = [
                'at' => $mood->created_at?->toIso8601String() ?? now()->toIso8601String(),
                'type' => 'mood',
                'title' => self::MOOD_LABELS[$mood->mood] ?? 'Mood logged',
                'description' => 'Mood logged',
                'time' => $mood->created_at?->format('H:i') ?? '',
                'icon' => self::MOOD_EMOJIS[$mood->mood] ?? '😐',
            ];
        }

        usort($items, fn ($a, $b) => strcmp($b['at'], $a['at']));

        return array_slice($items, 0, 6);
    }
}
