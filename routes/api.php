<?php

use App\Modules\Activities\Controllers\ActivityController;
use App\Modules\Events\Controllers\EventController;
use App\Modules\Events\Controllers\ProjectEventController;
use App\Modules\Faculties\Controllers\FacultyController;
use App\Modules\Universities\Controllers\UniversityController;
use Illuminate\Support\Facades\Route;
use App\Modules\Authentication\Controllers\AuthController;
use App\Modules\Reports\Controllers\ReportController;
use App\Modules\Reports\Controllers\EvaluatorController;
use App\Modules\Reports\Controllers\SemilleroController;
use App\Modules\Evaluations\Controllers\EvaluationController;
use App\Modules\Reports\Controllers\EventosController;
use App\Modules\Projects\Controllers\ProjectController;


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

});
Route::prefix('reports')->group(function () {
    Route::get('projects-with-authors', [ReportController::class, 'getProjectsWithAuthors']);
    Route::post('generate-certificate', [ReportController::class, 'generateCertificate']);
    Route::get('evaluators', [EvaluatorController::class, 'index']);
    Route::get('events/{eventId}/registered-users', [EventosController::class, 'getRegisteredUsers']);
    Route::get('/event/{eventoId}', [ReportController::class, 'getEventReport']);
    Route::get('/project-scores', [ReportController::class, 'getProjectScores']);
    Route::get('/activity', [ReportController::class, 'consultarActividades']);
    Route::get('/event/{eventoId}/certificados', [ReportController::class, 'generarCertificadosEvento']);
    Route::get('/certificados/{proyectoId}/{eventoId}', [ReportController::class, 'show']);
    Route::get('/semilleros/estructura', [SemilleroController::class, 'index']);
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


Route::prefix('projects')->group(function () {
    Route::middleware(['auth:api', 'roles:Coordinador de Proyecto,Administrador'])->get('/', [ProjectController::class, 'getAllProjects'])->name('projects.getAllProjects');
    Route::middleware(['auth:api', 'roles:Coordinador de Proyecto,Administrador'])->get('/{id}', [ProjectController::class, 'getProjectById'])->name('projects.getProjectById');

    Route::middleware(['auth:api', 'roles:Lider de Proyecto,Administrador'])->post('/{id}', [ProjectController::class, 'storeProject'])->name('projects.storeProject');
    Route::middleware(['auth:api', 'roles:Coordinador de Proyecto,Administrador'])->post('/{id}/asignar-estudiantes', [ProjectController::class, 'assignStudentToProject']);

    Route::middleware(['auth:api', 'roles:Coordinador de Proyecto,Administrador'])->put('/{id}/status', [ProjectController::class, 'updateStatus'])->name('projects.updateStatus');
    Route::middleware(['auth:api', 'roles:Coordinador de Proyecto,Administrador'])->put('/{id}', [ProjectController::class, 'updateProject'])->name('projects.updateProject');
});
