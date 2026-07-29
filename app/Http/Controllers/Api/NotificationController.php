<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'data' => $n->data,
                'created_at' => $n->created_at->toIso8601String(),
            ]);

        return response()->json(['success' => true, 'notifications' => $notifications]);
    }

    public function read(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function updateReminderSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reminder_enabled' => 'sometimes|boolean',
            'reminder_time' => 'sometimes|date_format:H:i:s',
            'timezone' => 'sometimes|string|max:64',
        ]);

        $user = $request->user();
        $user->update($validated);

        return response()->json([
            'success' => true,
            'user' => [
                'reminder_enabled' => $user->reminder_enabled,
                'reminder_time' => $user->reminder_time,
                'timezone' => $user->timezone,
            ],
        ]);
    }
}
