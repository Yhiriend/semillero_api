<?php

use App\Modules\Activities\Controllers\ActivityController;
use App\Modules\Events\Controllers\EventController;
use App\Modules\Events\Controllers\ProjectEventController;
use Illuminate\Support\Facades\Route;
use App\Modules\Authentication\Controllers\AuthController;
use App\Modules\Reports\Controllers\ReportController;
use App\Modules\Reports\Controllers\EvaluatorController;


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
        Route::get('/report/event/{eventoId}', [ReportController::class, 'getEventReport']);
        Route::get('/report/project-scores', [ReportController::class, 'getProjectScores']);
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


Route::prefix('events')->middleware(['auth:api', 'roles:Coordinador de Eventos'])->group(function () {

    Route::get('/', [EventController::class, 'index']);
    Route::post('/', [EventController::class, 'store']);
    Route::get('/{event}', [EventController::class, 'show']);
    Route::put('/{event}', [EventController::class, 'update']);
    Route::delete('/{event}', [EventController::class, 'destroy']);


    Route::prefix('{event}/activities')->group(function () {
        Route::get('/', [ActivityController::class, 'index']);
        Route::post('/', [ActivityController::class, 'store']);
        Route::get('/{activity}', [ActivityController::class, 'show']);
        Route::put('/{activity}', [ActivityController::class, 'update']);
        Route::delete('/{activity}', [ActivityController::class, 'destroy']);
        Route::post('/{activity}/assign-responsables', [ActivityController::class, 'assignResponsables']);
    });

    Route::prefix('{event}/projects')->group(function () {
        Route::get('/', [ProjectEventController::class, 'index']); 
        Route::post('/', [ProjectEventController::class, 'store']);
        Route::get('/{project}', [ProjectEventController::class, 'show']);
        Route::delete('/{project}', [ProjectEventController::class, 'destroy']);
    });

});