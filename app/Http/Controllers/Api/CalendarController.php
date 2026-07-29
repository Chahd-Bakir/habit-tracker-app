<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class CalendarController extends Controller
{
    public function month(Request $request): JsonResponse
    {
        $request->validate(['month' => 'nullable|date_format:Y-m']);

        $date = $request->month ? Carbon::parse($request->month.'-01') : now()->startOfMonth();
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

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

        $completions = [];

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $key = $day->toDateString();
            $logsForDay = $completedByDay[$key] ?? [];
            $total = 0;
            $done = 0;

            foreach ($habits as $habit) {
                if ($habit->created_at->copy()->startOfDay()->gt($day)) {
                    continue;
                }
                if ($habit->deleted_at && Carbon::parse($habit->deleted_at)->startOfDay()->lt($day)) {
                    continue;
                }

                $scheduled = match ($habit->frequency) {
                    'daily' => true,
                    'weekly' => true,
                    'custom' => in_array(strtolower($day->format('l')), $habit->custom_days ?? []),
                    default => false,
                };

                if (!$scheduled) {
                    continue;
                }

                $total++;
                if (in_array($habit->id, $logsForDay)) {
                    $done++;
                }
            }

            $completions[$key] = [
                'completed' => $done,
                'total' => $total,
            ];
        }

        return response()->json([
            'success' => true,
            'month' => $date->format('Y-m'),
            'days_in_month' => $date->daysInMonth,
            'first_day_of_week' => $start->dayOfWeek,
            'completions' => $completions,
            'habits' => $habits->map(fn ($h) => [
                'id' => $h->id,
                'title' => $h->title,
                'icon' => $h->icon,
                'color' => $h->color,
            ]),
        ]);
    }

    public function day(Request $request): JsonResponse
    {
        $validated = $request->validate(['date' => 'required|date_format:Y-m-d']);

        $date = Carbon::parse($validated['date']);
        $habits = $request->user()->habits()->orderBy('position')->get();

        $completedIds = $request->user()->habits()
            ->join('habit_logs', 'habits.id', '=', 'habit_logs.habit_id')
            ->whereDate('habit_logs.completed_date', $date)
            ->pluck('habit_logs.habit_id')
            ->toArray();

        $habits = $habits->map(fn ($h) => [
            'id' => $h->id,
            'title' => $h->title,
            'icon' => $h->icon,
            'color' => $h->color,
            'completed' => in_array($h->id, $completedIds),
        ]);

        return response()->json([
            'success' => true,
            'date' => $date->toDateString(),
            'habits' => $habits,
        ]);
    }
}
