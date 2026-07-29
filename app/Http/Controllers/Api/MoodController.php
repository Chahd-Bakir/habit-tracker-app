<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mood;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Storage;

class MoodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $moods = $request->user()->moods()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'moods' => $moods,
        ]);
    }

    public function today(Request $request): JsonResponse
    {
        $moods = $request->user()->moods()
            ->whereDate('date', now()->toDateString())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'moods' => $moods,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mood' => 'required|integer|between:1,5',
            'note' => 'nullable|string|max:1000',
        ]);

        $mood = $request->user()->moods()->create([
            'mood' => $validated['mood'],
            'note' => $validated['note'] ?? null,
            'date' => now()->toDateString(),
        ]);

        DatabaseNotification::where('notifiable_id', $request->user()->id)
            ->where('notifiable_type', $request->user()::class)
            ->where('type', 'mood_reminder')
            ->whereDate('created_at', today())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'mood' => $mood,
        ], 201);
    }

    public function update(Request $request, Mood $mood): JsonResponse
    {
        if ($mood->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'mood' => 'required|integer|between:1,5',
            'note' => 'nullable|string|max:1000',
        ]);

        $mood->update([
            'mood' => $validated['mood'],
            'note' => $validated['note'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'mood' => $mood,
        ]);
    }

    public function destroy(Request $request, Mood $mood): JsonResponse
    {
        if ($mood->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $mood->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mood entry deleted successfully.',
        ]);
    }

    public function uploadPhoto(Request $request, Mood $mood): JsonResponse
    {
        if ($mood->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        if ($mood->photo_path) {
            Storage::disk('public')->delete($mood->photo_path);
        }

        $path = $request->file('photo')->store('moods/photos', 'public');

        $mood->update(['photo_path' => $path]);
        $mood->refresh();

        return response()->json([
            'success' => true,
            'mood' => $mood,
        ]);
    }

    public function deletePhoto(Request $request, Mood $mood): JsonResponse
    {
        if ($mood->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        if ($mood->photo_path) {
            Storage::disk('public')->delete($mood->photo_path);
        }

        $mood->update(['photo_path' => null]);
        $mood->refresh();

        return response()->json([
            'success' => true,
            'mood' => $mood,
        ]);
    }

    public function uploadVoice(Request $request, Mood $mood): JsonResponse
    {
        if ($mood->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'voice' => 'required|file|mimes:webm,mp3,wav,ogg|max:5120',
        ]);

        if ($mood->voice_note_path) {
            Storage::disk('public')->delete($mood->voice_note_path);
        }

        $path = $request->file('voice')->store('moods/voices', 'public');

        $mood->update(['voice_note_path' => $path]);
        $mood->refresh();

        return response()->json([
            'success' => true,
            'mood' => $mood,
        ]);
    }

    public function deleteVoice(Request $request, Mood $mood): JsonResponse
    {
        if ($mood->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        if ($mood->voice_note_path) {
            Storage::disk('public')->delete($mood->voice_note_path);
        }

        $mood->update(['voice_note_path' => null]);
        $mood->refresh();

        return response()->json([
            'success' => true,
            'mood' => $mood,
        ]);
    }
}
