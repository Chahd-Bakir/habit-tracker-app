<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\Mood;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminStatsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $start = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subDays(30);
        $end = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now();

        $totalUsers = User::count();

        $newUsersToday = User::whereDate('created_at', Carbon::today())->count();

        $newUsersThisWeek = User::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();

        $dau = Mood::whereDate('created_at', Carbon::today())
            ->distinct('user_id')
            ->count('user_id');

        $threeWeeksAgo = $start->copy()->subWeek();
        $twoWeeksAgo = $end->copy()->subWeek();

        $activeLastWeek = Mood::whereBetween('created_at', [$threeWeeksAgo, $twoWeeksAgo])
            ->distinct('user_id')
            ->pluck('user_id');

        $activeThisWeek = Mood::whereBetween('created_at', [$start, $end])
            ->distinct('user_id')
            ->pluck('user_id');

        $retained = $activeThisWeek->intersect($activeLastWeek);
        $retentionRate = $activeLastWeek->count() > 0
            ? round(($retained->count() / $activeLastWeek->count()) * 100, 1)
            : 0;

        $newUsersOverTime = User::whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->map(fn ($r) => (int) $r->count);

        $dates = [];
        $newUsers = [];
        $cursor = $start->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $dates[] = $key;
            $newUsers[] = $newUsersOverTime[$key] ?? 0;
            $cursor->addDay();
        }

        $roleDistribution = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->get()
            ->map(fn ($r) => [
                'role' => $r->role ?? 'user',
                'label' => ucfirst($r->role ?? 'user'),
                'count' => (int) $r->count,
            ]);

        $topHabits = Habit::selectRaw('title, COUNT(*) as count')
            ->groupBy('title')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'title' => $r->title,
                'count' => (int) $r->count,
            ]);

        $activityByDay = HabitLog::whereBetween('completed_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('DATE(completed_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->map(fn ($r) => (int) $r->count);

        $activity = [];
        foreach ($dates as $date) {
            $activity[] = $activityByDay[$date] ?? 0;
        }

        [$weekdayLabels, $weekdayRates] = $this->weekdayCompletionRates($start, $end);

        return response()->json([
            'success' => true,
            'stats' => [
                'total_users' => $totalUsers,
                'new_users_today' => $newUsersToday,
                'new_users_this_week' => $newUsersThisWeek,
                'dau' => $dau,
                'retention_rate' => $retentionRate,
                'premium_subscribers_count' => 0,
                'revenue' => 0,
            ],
            'chart' => [
                'labels' => $dates,
                'datasets' => [
                    [
                        'label' => 'New users',
                        'data' => $newUsers,
                    ],
                ],
            ],
            'role_distribution' => $roleDistribution,
            'top_habits' => $topHabits,
            'activity' => [
                'labels' => $dates,
                'data' => $activity,
            ],
            'weekday_completion' => [
                'labels' => $weekdayLabels,
                'data' => $weekdayRates,
            ],
            'period' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
        ]);
    }

    private function weekdayCompletionRates(Carbon $start, Carbon $end): array
    {
        $habits = Habit::query()->get(['id', 'frequency', 'custom_days', 'created_at', 'deleted_at']);

        $logs = HabitLog::whereBetween('completed_date', [$start->toDateString(), $end->toDateString()])
            ->get(['habit_id', 'completed_date']);

        $scheduledByWeekday = array_fill(1, 7, 0);
        $doneByWeekday = array_fill(1, 7, 0);

        for ($day = $start->copy()->startOfDay(); $day->lte($end); $day->addDay()) {
            $weekday = $day->dayOfWeekIso;

            $scheduled = 0;
            foreach ($habits as $habit) {
                if ($habit->created_at->copy()->startOfDay()->gt($day)) {
                    continue;
                }
                if ($habit->deleted_at && Carbon::parse($habit->deleted_at)->startOfDay()->lt($day)) {
                    continue;
                }

                $scheduledOnDay = match ($habit->frequency) {
                    'daily' => true,
                    'weekly' => true,
                    'custom' => in_array(strtolower($day->format('l')), $habit->custom_days ?? []),
                    default => false,
                };

                if ($scheduledOnDay) {
                    $scheduled++;
                }
            }

            $scheduledByWeekday[$weekday] += $scheduled;
        }

        foreach ($logs as $log) {
            $doneByWeekday[Carbon::parse($log->completed_date)->dayOfWeekIso]++;
        }

        $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $rates = [];

        for ($i = 1; $i <= 7; $i++) {
            $rates[] = $scheduledByWeekday[$i] > 0
                ? round(($doneByWeekday[$i] / $scheduledByWeekday[$i]) * 100, 1)
                : 0;
        }

        return [$labels, $rates];
    }
}
