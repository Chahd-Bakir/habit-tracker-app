<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function week(Request $request): JsonResponse
    {
        $request->validate(['date' => 'nullable|date_format:Y-m-d']);

        $ref = $request->date ? Carbon::parse($request->date) : now();
        $start = $ref->copy()->startOfWeek();
        $end = $ref->copy()->endOfWeek();

        $habits = $request->user()->habits()->orderBy('position')->get();

        $logs = $request->user()->habits()
            ->join('habit_logs', 'habits.id', '=', 'habit_logs.habit_id')
            ->whereDate('habit_logs.completed_date', '>=', $start)
            ->whereDate('habit_logs.completed_date', '<=', $end)
            ->get(['habit_logs.habit_id', 'habit_logs.completed_date']);

        $completedByDay = [];
        foreach ($logs as $log) {
            $key = Carbon::parse($log->completed_date)->toDateString();
            $completedByDay[$key][] = $log->habit_id;
        }

        $totalExpected = 0;
        $totalCheckins = 0;
        $bestDay = null;
        $bestRatio = -1;
        $bestCompleted = 0;
        $dailyBreakdown = [];

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $key = $day->toDateString();
            $logsForDay = $completedByDay[$key] ?? [];
            $dayTotal = 0;
            $dayDone = 0;

            foreach ($habits as $habit) {
                if ($habit->created_at->copy()->startOfDay()->gt($day)) continue;
                if ($habit->deleted_at && Carbon::parse($habit->deleted_at)->startOfDay()->lt($day)) continue;

                $scheduled = match ($habit->frequency) {
                    'daily' => true,
                    'custom' => in_array(strtolower($day->format('l')), $habit->custom_days ?? []),
                    default => false,
                };

                if (!$scheduled) continue;

                $dayTotal++;
                if (in_array($habit->id, $logsForDay)) {
                    $dayDone++;
                }
            }

            $totalExpected += $dayTotal;
            $totalCheckins += $dayDone;

            $dailyBreakdown[] = [
                'date' => $key,
                'label' => $day->format('D'),
                'completed' => $dayDone,
                'total' => $dayTotal,
            ];

            $ratio = $dayTotal > 0 ? $dayDone / $dayTotal : 0;
            if ($dayTotal > 0 && ($ratio > $bestRatio || ($ratio === $bestRatio && $dayDone > $bestCompleted))) {
                $bestRatio = $ratio;
                $bestCompleted = $dayDone;
                $bestDay = ['date' => $key, 'label' => $day->format('l')];
            }
        }

        $percentage = $totalExpected > 0 ? round(($totalCheckins / $totalExpected) * 100) : 0;

        return response()->json([
            'success' => true,
            'week_start' => $start->toDateString(),
            'week_end' => $end->toDateString(),
            'completion_percentage' => $percentage,
            'best_day' => $bestDay,
            'total_checkins' => $totalCheckins,
            'daily_breakdown' => $dailyBreakdown,
        ]);
    }
}
