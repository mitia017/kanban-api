<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::apiResource('kanbans', KanbanController::class);

    Route::prefix('kanbans/{kanban}')->group(function () {
        Route::get('columns', [ColumnController::class, 'index']);
        Route::post('columns', [ColumnController::class, 'store']);
    });

    Route::prefix('columns/{column}')->group(function () {
        Route::get('tasks', [TaskController::class, 'index']);
        Route::put('', [ColumnController::class, 'update']);
        Route::delete('', [ColumnController::class, 'destroy']);
        Route::post('tasks', [TaskController::class, 'store']);
    });

    Route::prefix('tasks/{task}')->group(function () {
        Route::put('', [TaskController::class, 'update']);
        Route::delete('', [TaskController::class, 'destroy']);
    });

    Route::patch('tasks/reorder', [TaskController::class, 'reorder']);
    Route::patch('columns/reorder', [ColumnController::class, 'reorder']);
});
