<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
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
            'period' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
        ]);
    }
}
