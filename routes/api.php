<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\Admin\AdminStatsController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\SuggestionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\HabitController;
use App\Http\Controllers\Api\MoodController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PromptController;
use App\Http\Controllers\Api\StatsController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/admin/login', [AdminController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);

    Route::post('/habits/reorder', [HabitController::class, 'reorder']);
    Route::post('/habits/{habit}/toggle', [HabitController::class, 'toggle']);

    Route::apiResource('habits', HabitController::class);
    Route::get('/suggestions', [SuggestionController::class, 'index']);

    Route::get('/calendar/month', [CalendarController::class, 'month']);
    Route::get('/calendar/day', [CalendarController::class, 'day']);

    Route::get('/stats/weekly', [StatsController::class, 'week']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'read']);
    Route::put('/reminder-settings', [NotificationController::class, 'updateReminderSettings']);

    Route::get('/daily-prompt', [PromptController::class, 'today']);

    Route::get('/moods/today', [MoodController::class, 'today']);
    Route::post('/moods/{mood}/photo', [MoodController::class, 'uploadPhoto']);
    Route::delete('/moods/{mood}/photo', [MoodController::class, 'deletePhoto']);
    Route::post('/moods/{mood}/voice', [MoodController::class, 'uploadVoice']);
    Route::delete('/moods/{mood}/voice', [MoodController::class, 'deleteVoice']);
    Route::apiResource('moods', MoodController::class)->only(['index', 'store', 'update', 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/users', [AdminController::class, 'users']);
    Route::get('/stats', [AdminStatsController::class, 'index']);
    Route::apiResource('categories', CategoryController::class)->except(['show']);
    Route::apiResource('suggestions', SuggestionController::class)->except(['show']);
});

