<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HabitCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = HabitCategory::withCount('suggestions')
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'categories' => $categories]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:habit_categories,name',
        ]);

        $category = HabitCategory::create($validated);

        return response()->json(['success' => true, 'category' => $category], 201);
    }

    public function update(Request $request, HabitCategory $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:habit_categories,name,' . $category->id,
        ]);

        $category->update($validated);

        return response()->json(['success' => true, 'category' => $category]);
    }

    public function destroy(HabitCategory $category): JsonResponse
    {
        $category->delete();

        return response()->json(['success' => true, 'message' => 'Category deleted.']);
    }
}
