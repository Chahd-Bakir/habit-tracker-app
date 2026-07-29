<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHabitRequest;
use App\Models\Habit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HabitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $habits = $request->user()->habits()->orderBy('position')->get();
        $today = now()->startOfDay();
        $changed = false;

        foreach ($habits as $habit) {
            if ($habit->status === 'done') {
                $logToday = $habit->logs()->whereDate('completed_date', $today)->exists();
                if (!$logToday) {
                    $habit->status = 'todo';
                    $changed = true;
                }
            }

            if ($habit->recalculateStreak()) {
                $changed = true;
            }
        }

        if ($changed) {
            $habits = $request->user()->habits()->orderBy('position')->get();
        }

        return response()->json([
            'success' => true,
            'habits' => $habits,
        ]);
    }

    public function store(StoreHabitRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['position'] = ($request->user()->habits()->max('position') ?? -1) + 1;
        $habit = $request->user()->habits()->create($data);

        return response()->json([
            'success' => true,
            'habit' => $habit,
        ], 201);
    }

    public function show(Request $request, Habit $habit): JsonResponse
    {
        if ($habit->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'habit' => $habit,
        ]);
    }

    public function update(StoreHabitRequest $request, Habit $habit): JsonResponse
    {
        if ($habit->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $habit->update($request->validated());

        return response()->json([
            'success' => true,
            'habit' => $habit,
        ]);
    }

    public function toggle(Request $request, Habit $habit): JsonResponse
    {
        if ($habit->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $today = now()->startOfDay();
        $log = $habit->logs()->whereDate('completed_date', $today)->first();

        if ($log) {
            $log->delete();
            $habit->update(['status' => 'todo']);
            $completed = false;
        } else {
            $habit->logs()->create(['completed_date' => $today]);
            $habit->update(['status' => 'done']);
            $completed = true;
        }

        $habit->recalculateStreak();
        $habit->refresh();

        return response()->json([
            'success' => true,
            'completed' => $completed,
            'habit' => $habit,
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'habits' => 'required|array',
            'habits.*.id' => 'required|integer|exists:habits,id',
            'habits.*.position' => 'required|integer|min:0',
        ]);

        $ids = collect($validated['habits'])->pluck('id');
        $userHabits = $request->user()->habits()->whereIn('id', $ids)->get()->keyBy('id');

        foreach ($validated['habits'] as $item) {
            if ($userHabits->has($item['id'])) {
                $userHabits[$item['id']]->update(['position' => $item['position']]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, Habit $habit): JsonResponse
    {
        if ($habit->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $habit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Habit deleted successfully.',
        ]);
    }
}
