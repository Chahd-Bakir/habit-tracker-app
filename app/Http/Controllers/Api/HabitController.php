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
        $habits = $request->user()->habits()->latest()->get();

        return response()->json([
            'success' => true,
            'habits' => $habits,
        ]);
    }

    public function store(StoreHabitRequest $request): JsonResponse
    {
        $habit = $request->user()->habits()->create($request->validated());

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

    public function update(Request $request, Habit $habit): JsonResponse
    {
        if ($habit->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $habit->update($request->only(['title', 'icon', 'color', 'frequency', 'custom_days', 'reminder_time', 'status']));

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

        $habit->refresh();

        return response()->json([
            'success' => true,
            'completed' => $completed,
            'habit' => $habit,
        ]);
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
