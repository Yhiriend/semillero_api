<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Authentication\Controllers\AuthController;
use App\Modules\Reports\Controllers\ReportController;
use App\Modules\Reports\Controllers\EvaluatorController;
use App\Modules\Events\Controllers\EventController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::prefix('reports')->group(function () {
        Route::get('projects-with-authors', [ReportController::class, 'getProjectsWithAuthors']);
        Route::post('generate-certificate', [ReportController::class, 'generateCertificate']);
        Route::get('evaluators', [EvaluatorController::class, 'index']);
        Route::get('events/{eventId}/registered-users', [EventController::class, 'getRegisteredUsers']);
    });
});
