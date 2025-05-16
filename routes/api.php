<?php

use App\Modules\Seedbeds\Controllers\InscriptionController;
use App\Modules\Seedbeds\Controllers\SeedbedsController;

use Illuminate\Support\Facades\Route;







































































































Route::middleware(['auth:api'])->group(function () {

    Route::middleware(['roles:Integrante Semillero'])->group(function () {
        Route::get('/seedbeds', [SeedbedsController::class, 'index'])->name('semilleros.index');
        Route::get('/seedbeds/{id}', [SeedbedsController::class, 'show'])->name('semilleros.show');
    });

    Route::middleware(['roles:Coordinador de Semillero'])->group(function () {
        Route::post('/seedbeds', [SeedbedsController::class, 'store'])->name('semilleros.store');
        Route::put('/seedbeds/{id}', [SeedbedsController::class, 'update'])->name('semilleros.update');
        Route::delete('/seedbeds/{id}', [SeedbedsController::class, 'delete'])->name('semilleros.delete');

        Route::get('/inscriptions', [InscriptionController::class, 'index'])->name('inscripciones.index');
        Route::post('/inscriptions', [InscriptionController::class, 'store'])->name('inscripciones.store');
    });

});














