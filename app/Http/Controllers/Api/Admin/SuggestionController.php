<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HabitSuggestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuggestionController extends Controller
{
    public function index(): JsonResponse
    {
        $suggestions = HabitSuggestion::with('category')
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'suggestions' => $suggestions]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'category_id' => 'nullable|exists:habit_categories,id',
        ]);

        $suggestion = HabitSuggestion::create($validated);

        return response()->json(['success' => true, 'suggestion' => $suggestion->load('category')], 201);
    }

    public function update(Request $request, HabitSuggestion $suggestion): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'category_id' => 'nullable|exists:habit_categories,id',
        ]);

        $suggestion->update($validated);

        return response()->json(['success' => true, 'suggestion' => $suggestion->load('category')]);
    }

    public function destroy(HabitSuggestion $suggestion): JsonResponse
    {
        $suggestion->delete();

        return response()->json(['success' => true, 'message' => 'Suggestion deleted.']);
    }
}
