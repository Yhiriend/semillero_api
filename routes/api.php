<?php

use App\Modules\Evaluations\Controllers\EvaluationController;
use App\Modules\Events\Controllers\EventController;
use App\Modules\Events\Controllers\ProjectEventController;
use App\Modules\Faculties\Controllers\FacultyController;
use App\Modules\Programs\Controllers\ProgramController;
use App\Modules\Universities\Controllers\UniversityController;
use Illuminate\Support\Facades\Route;
use App\Modules\Authentication\Controllers\AuthController;
use App\Modules\Projects\Controllers\ProjectController;
use App\Modules\Users\Controllers\UserController;
use App\Modules\Reports\Controllers\ReportController;


Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot', [AuthController::class, 'forgotPassword']);
    Route::post('reset', [AuthController::class, 'resetPassword']);
    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::prefix('users')->middleware(['auth:api', 'roles:Administrador'])->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

Route::prefix('events')->middleware(['auth:api', 'roles:Coordinador de Eventos'])->group(function () {

    Route::get('/', [EventController::class, 'index']);
    Route::post('/', [EventController::class, 'store']);
    Route::get('/{event}', [EventController::class, 'show']);
    Route::put('/{event}', [EventController::class, 'update']);
    Route::delete('/{event}', [EventController::class, 'destroy']);

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


Route::prefix('projects')->group(function () {
    Route::middleware(['auth:api', 'roles:Coordinador de Proyecto,Administrador'])->get('/', [ProjectController::class, 'getAllProjects'])->name('projects.getAllProjects');
    Route::middleware(['auth:api', 'roles:Coordinador de Proyecto,Administrador'])->get('/{id}', [ProjectController::class, 'getProjectById'])->name('projects.getProjectById');

    Route::middleware(['auth:api', 'roles:Lider de Proyecto,Administrador'])->post('/{id}', [ProjectController::class, 'storeProject'])->name('projects.storeProject');
    Route::middleware(['auth:api', 'roles:Coordinador de Proyecto,Administrador'])->post('/{id}/asignar-estudiantes', [ProjectController::class, 'assignStudentToProject']);

    Route::middleware(['auth:api', 'roles:Coordinador de Proyecto,Administrador'])->put('/{id}/status', [ProjectController::class, 'updateStatus'])->name('projects.updateStatus');
    Route::middleware(['auth:api', 'roles:Coordinador de Proyecto,Administrador'])->put('/{id}', [ProjectController::class, 'updateProject'])->name('projects.updateProject');

});

Route::prefix('evaluations')->group(function () {
    Route::get('/', [EvaluationController::class, 'index']);
    Route::post('/', [EvaluationController::class, 'store']);
    Route::get('/{id}', [EvaluationController::class, 'show']);
    Route::put('/{id}', [EvaluationController::class, 'update']);
    Route::delete('/{id}', [EvaluationController::class, 'destroy']);


    Route::post('/{id}/cancel', [EvaluationController::class, 'cancel']);
    Route::post('/{id}/complete', [EvaluationController::class, 'completeEvaluation']);
    Route::post('/{id}/reassign', [EvaluationController::class, 'reassign']);


    Route::get('/project/{projectId}', [EvaluationController::class, 'byProject']);
    Route::get('/evaluator/{evaluatorId}', [EvaluationController::class, 'byEvaluator']);
    Route::get('/evaluator/{evaluatorId}/performance', [EvaluationController::class, 'evaluatorPerformance']);
    Route::get('/project/{projectId}/metrics', [EvaluationController::class, 'metricsByStatus']);
    Route::get('/status/{status}', [EvaluationController::class, 'byStatus']);


    Route::get('/project/{projectId}/available-evaluators', [EvaluationController::class, 'availableEvaluators']);


    Route::post('/event/{eventId}/mass-assign', [EvaluationController::class, 'massAssign']);


    Route::get('/dashboard/stats', [EvaluationController::class, 'dashboardStats']);
    Route::get('/event/{eventId}/report', [EvaluationController::class, 'generateReport']);
    Route::get('/event/{eventId}/unevaluated-projects', [EvaluationController::class, 'unevaluatedProjects']);
});

Route::prefix('reports')->middleware(['auth:api'])->group(function () {
    // Certificate routes
    Route::prefix('certificates')->group(function () {
        Route::post('/generate', [ReportController::class, 'generateCertificate']);
        Route::get('/event/{eventId}/generate-all', [ReportController::class, 'generarCertificadosEvento']);
        Route::get('/project/{projectId}/event/{eventId}', [ReportController::class, 'show']);
    });

    // Event reports
    Route::prefix('events')->group(function () {
        Route::get('/{eventId}/report', [ReportController::class, 'getEventReport']);
        Route::get('/{eventId}/activities', [ReportController::class, 'consultarActividades']);
    });

    // Project reports
    Route::prefix('projects')->group(function () {
        Route::get('/with-authors', [ReportController::class, 'getProjectsWithAuthors']);
        Route::get('/scores', [ReportController::class, 'getProjectScores']);
    });
});
