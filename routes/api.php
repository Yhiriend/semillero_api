<?php

use App\Modules\GestionDeSemilleros\Controllers\EvaluacionController;
use App\Modules\GestionDeSemilleros\Controllers\InscripcionController;
use App\Modules\GestionDeSemilleros\Controllers\ProyectoController;
use App\Modules\GestionDeSemilleros\Controllers\SemilleroApiController;
use App\Modules\GestionDeSemilleros\Controllers\ActividadController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// SEMILLEROS (Integrante Semillero y Coordinador Semillero)
Route::middleware(['auth:api'])->group(function () {

    // Integrante Semillero: puede consultar
    Route::middleware(['check.api.role:Integrante Semillero'])->group(function () {
        Route::get('/semilleros', [SemilleroApiController::class, 'index'])->name('semilleros.index');
        Route::get('/semilleros/{id}', [SemilleroApiController::class, 'show'])->name('semilleros.show');
    });

    // Coordinador de Semillero: puede crear, actualizar, eliminar
    Route::middleware(['check.api.role:Coordinador de Semillero'])->group(function () {
        Route::post('/semilleros', [SemilleroApiController::class, 'store'])->name('semilleros.store');
        Route::put('/semilleros/{id}', [SemilleroApiController::class, 'update'])->name('semilleros.update');
        Route::delete('/semilleros/{id}', [SemilleroApiController::class, 'delete'])->name('semilleros.delete');

        // Inscripciones
        Route::get('/inscripciones', [InscripcionController::class, 'index'])->name('inscripciones.index');
        Route::post('/inscripciones', [InscripcionController::class, 'store'])->name('inscripciones.store');
    });

});


// PROYECTOS (Todos pueden consultar, Coordinador Proyecto gestiona)

Route::middleware(['auth:api'])->group(function () {

    // Consultar proyectos (disponible para todos los autenticados)
    Route::get('/proyectos', [ProyectoController::class, 'index'])->name('proyectos.index');

    // Coordinador de Proyecto: crear, asignar, evaluar
    Route::middleware(['check.api.role:Coordinador de Proyecto'])->group(function () {
        Route::post('/proyectos', [ProyectoController::class, 'store'])->name('proyectos.store');
        Route::post('/proyectos/{id}/asignar-estudiantes', [ProyectoController::class, 'asignarEstudiantes'])->name('proyectos.asignar_estudiantes');
        Route::put('/proyectos/{id}/evaluar', [ProyectoController::class, 'evaluar'])->name('proyectos.evaluar');

        // Actividades y evaluaciones (registrar)
        Route::post('/actividades', [ActividadController::class, 'registrar']);
        Route::post('/evaluaciones', [EvaluacionController::class, 'registrar']);

        // Listar actividades y evaluaciones
        Route::get('/actividades', [ActividadController::class, 'listar']);
        Route::get('/evaluaciones', [EvaluacionController::class, 'listar']);
    });

});




