<?php

use App\Modules\Activities\Controllers\ActivityController;
use App\Modules\Events\Controllers\EventController;
use App\Modules\Events\Controllers\ProjectEventController;
use App\Modules\Faculties\Controllers\FacultyController;
use App\Modules\Programs\Controllers\ProgramController;
use App\Modules\Universities\Controllers\UniversityController;
use Illuminate\Support\Facades\Route;
use App\Modules\Authentication\Controllers\AuthController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('logout', [AuthController::class, 'logout']);
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


Route::prefix('universities')->middleware(['auth:api', 'roles:Administrador'])->group(function () {
    Route::get('/', [UniversityController::class, 'index']);
    Route::post('/', [UniversityController::class, 'store']);
    Route::get('/{university}', [UniversityController::class, 'show']);
    Route::put('/{university}', [UniversityController::class, 'update']);
    Route::delete('/{university}', [UniversityController::class, 'destroy']);
});

Route::prefix('faculties')->middleware(['auth:api', 'roles:Administrador'])->group(function () {
    Route::get('/', [FacultyController::class, 'index']);
    Route::post('/', [FacultyController::class, 'store']);
    Route::get('/{faculty}', [FacultyController::class, 'show']);
    Route::put('/{faculty}', [FacultyController::class, 'update']);
    Route::delete('/{faculty}', [FacultyController::class, 'destroy']);
});

Route::prefix('programs')->middleware(['auth:api', 'roles:Administrador'])->group(function () {
    Route::get('/', [ProgramController::class, 'index']);
    Route::post('/', [ProgramController::class, 'store']);
    Route::get('/{program}', [ProgramController::class, 'show']);
    Route::put('/{program}', [ProgramController::class, 'update']);
    Route::delete('/{program}', [ProgramController::class, 'destroy']);
});