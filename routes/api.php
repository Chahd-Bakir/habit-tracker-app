<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HabitController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);

    Route::post('/habits/{habit}/toggle', [HabitController::class, 'toggle']);

    Route::apiResource('habits', HabitController::class);
});

