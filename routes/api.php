<?php

use App\Modules\Events\Controllers\ActivityController;
use Illuminate\Support\Facades\Route;
use App\Modules\Authentication\Controllers\AuthController;
use App\Modules\Users\Controllers\UserController;
use App\Modules\Events\Controllers\EventController;
use App\Modules\Events\Controllers\ProjectEventController;
use App\Modules\Projects\Controllers\ProjectController;
use App\Modules\Faculties\Controllers\FacultyController;
use App\Modules\Programs\Controllers\ProgramController;
use App\Modules\Universities\Controllers\UniversityController;
use App\Modules\Evaluations\Controllers\EvaluationController;
use App\Modules\Reports\Controllers\ReportController;
use App\Modules\Seedbeds\Controllers\SeedbedsController;
use App\Modules\Seedbeds\Controllers\InscriptionController;
use App\Modules\Reports\Controllers\EvaluatorController;
use App\Modules\Reports\Controllers\EventInscriptionController;
use App\Modules\Reports\Controllers\ProjectReportController;
use App\Modules\Reports\Controllers\SeedbedsReportController; 
use App\Modules\Reports\Controllers\UniversityReportController;

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

Route::prefix('events')->middleware(['auth:api', 'roles:Coordinador de Eventos,Administrador'])->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::post('/', [EventController::class, 'store']);
    Route::get('/projects', [EventController::class, 'getProjects']);
    Route::get('/coordinators', [EventController::class, 'getCoordinators']);
    Route::get('/responsables', [EventController::class, 'getResponsables']);
    Route::get('/{event}', [EventController::class, 'show']);
    Route::put('/{event}', [EventController::class, 'update']);
    Route::delete('/{event}', [EventController::class, 'destroy']);

    Route::prefix('{event}/projects')->group(function () {
        Route::get('/', [ProjectEventController::class, 'index']);
        Route::post('/', [ProjectEventController::class, 'store']);
        Route::get('/{project}', [ProjectEventController::class, 'show']);
        Route::delete('/{project}', [ProjectEventController::class, 'destroy']);
    });

    Route::prefix('{event}/activities')->group(function () {
        Route::get('/', [ActivityController::class, 'index']);
        Route::post('/', [ActivityController::class, 'store']);
        Route::get('/{activity}', [ActivityController::class, 'show']);
        Route::put('/{activity}', [ActivityController::class, 'update']);
        Route::delete('/{activity}', [ActivityController::class, 'destroy']);
    });
});

Route::prefix('projects')->group(function () {
    Route::middleware(['auth:api', 'roles:Lider de Proyecto,Coordinador de Semillero,Coordinador de Proyecto,Administrador'])->get('/', [ProjectController::class, 'getAllProjects'])->name('projects.getAllProjects');
    Route::middleware(['auth:api', 'roles:Lider de Proyecto,Coordinador de Semillero,Coordinador de Proyecto,Administrador'])->get('/{id}', [ProjectController::class, 'getProjectById'])->name('projects.getProjectById');
    Route::middleware(['auth:api', 'roles:Lider de Proyecto,Coordinador de Semillero,Coordinador de Proyecto,Administrador'])->get('/{id}/users', [ProjectController::class, 'getProjectUsers']);
    Route::middleware(['auth:api', 'roles:Lider de Proyecto,Coordinador de Semillero,Administrador'])->get('/seedbed/{seedbedId}/integrantes', [ProjectController::class, 'getSeedbedIntegrantes']);
    Route::middleware(['auth:api', 'roles:Lider de Proyecto,Coordinador de Semillero,Administrador'])->get('/{LeaderId}/seedbeds', [ProjectController::class, 'getSeedbedProjects']);
    Route::middleware(['auth:api', 'roles:Coordinador de Proyecto,Coordinador de Semillero,Administrador'])->get('/seedbed/{seedbedId}/lideres', [ProjectController::class, 'getSeedbedLideres']);
    Route::middleware(['auth:api', 'roles:Lider de Proyecto,Coordinador de Semillero,Coordinador de Proyecto,Administrador'])->get('/{id}/evaluation', [ProjectController::class, 'getProjectEvaluation']);
    Route::middleware(['auth:api', 'roles:Lider de Proyecto,Coordinador de Semillero,Administrador'])->get('/seedbed/{seedbedId}/coordinadores', [ProjectController::class, 'getSeedbedCoordinadores']);

    Route::middleware(['auth:api', 'roles:Lider de Proyecto,Administrador'])->post('/{id}', [ProjectController::class, 'storeProject'])->name('projects.storeProject');
    Route::middleware(['auth:api', 'roles:Coordinador de Proyecto,Administrador'])->post('/{id}/asignar-estudiantes', [ProjectController::class, 'assignStudentToProject']);

    Route::middleware(['auth:api', 'roles:Coordinador de Proyecto,Administrador'])->put('/{id}/status', [ProjectController::class, 'updateStatus'])->name('projects.updateStatus');
    Route::middleware(['auth:api', 'roles:Coordinador de Proyecto,Administrador'])->put('/{id}', [ProjectController::class, 'updateProject'])->name('projects.updateProject');
});

Route::prefix('evaluations')->middleware(['auth:api', 'roles:Evaluador,Coordinador de Semillero'])->group(function () {
    Route::get('/', [EvaluationController::class, 'index']);
    Route::post('/', [EvaluationController::class, 'store']);
    Route::get('/roles', [EvaluationController::class, 'getRoleById']);
    Route::get('/projects', [EvaluationController::class, 'getAllProjects']);
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

Route::prefix('reports')->middleware(['auth:api', 'roles:Administrador'])->group(function () {
    // Certificate routes
    Route::prefix('certificates')->group(function () {
        Route::post('/generate', [ReportController::class, 'generateCertificate']);
        Route::get('/event/{eventId}/generate-all', [ReportController::class, 'generarCertificadosEvento']);
        Route::get('/project/{projectId}/event/{eventId}', [ReportController::class, 'show']);
    });

    // Seedbeds routes
    Route::prefix('seedbeds')->group(function () {
        Route::get('/', [SeedbedsReportController::class, 'getAllSeedbeds']);
        Route::get('/structure', [SeedbedsReportController::class, 'index']);
        Route::get('/{seedbedId}/users', [SeedbedsReportController::class, 'getUsersBySeedbed']);
    });

    // Projects routes
    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectReportController::class, 'getAllProjects']);
        Route::get('/scores', [ReportController::class, 'getProjectScores']);
        Route::get('/by-seedbed', [ReportController::class, 'getProjectsBySemillero']);
        Route::get('/with-authors', [ReportController::class, 'getProjectsWithAuthors']);
    });

     // Universities routes
    Route::prefix('universities')->group(function () {
        Route::get('/', [UniversityReportController   ::class, 'index']);
    });

    // Events routes
    Route::prefix('events')->group(function () {
        Route::get('/{eventId}/report', [ReportController::class, 'getEventReport']);
        Route::get('/{eventId}/activities', [ReportController::class, 'consultarActividades']);
        Route::get('/activities', [ReportController::class, 'consultarActividades']);
        Route::get('/enrolled-students', [EventInscriptionController::class, 'getEnrolledStudents']);
    });

    // Evaluators routes
    Route::prefix('evaluators')->group(function () {
        Route::get('/with-projects', [EvaluatorController::class, 'index']);
    });
});

Route::middleware(['auth:api'])->group(function () {

    Route::middleware(['roles:Integrante Semillero'])->group(function () {
        Route::get('/seedbeds', [SeedbedsController::class, 'index'])->name('semilleros.index');
        Route::get('/seedbeds/{id}', [SeedbedsController::class, 'show'])->name('semilleros.show');
        Route::get('/seedbeds/{id}/students', [InscriptionController::class, 'studentsBySeedbed']);
    });


    Route::middleware(['roles:Coordinador de Semillero'])->group(function () {
        Route::post('/seedbeds', [SeedbedsController::class, 'store']);
        Route::put('/seedbeds/{id}', [SeedbedsController::class, 'update']);
        Route::delete('/seedbeds/{id}', [SeedbedsController::class, 'delete']);
        Route::get('/seedbeds/{id}/inscriptions', [InscriptionController::class, 'index']);
        Route::post('/seedbeds/{id}/inscriptions', [InscriptionController::class, 'store']);
        Route::get('seedbeds/{id}/students', [InscriptionController::class, 'students']);
        Route::delete('/inscriptions/{semillero_id}/{usuario_id}', [InscriptionController::class, 'destroy'])->name('inscripciones.destroy');
        Route::put('/inscriptions/{id}', [InscriptionController::class, 'update']);
        Route::delete('/inscriptions/{id}', [InscriptionController::class, 'destroy']);
        Route::get('/inscriptions', [InscriptionController::class, 'index'])->name('inscripciones.index');
        Route::post('/inscriptions', [InscriptionController::class, 'store'])->name('inscripciones.store');
    });
});
