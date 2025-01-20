<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;

// Rutas públicas
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Rutas protegidas
Route::middleware(['jwt.verify'])->group(function () {
    // Auth routes
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('profile', [AuthController::class, 'profile']);

    // Task routes
    Route::apiResource('tasks', TaskController::class);
    Route::put('/tasks/{task}/toggle-status', [TaskController::class, 'toggleStatus']);
});