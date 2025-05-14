<?php

use Illuminate\Support\Facades\Route;
use App\Modules\GestionDeSemilleros\Controllers\SemilleroController;
use App\Modules\GestionDeSemilleros\Controllers\CreateController;


Route::get('/', function () {
    return view('welcome');
});


/*Route::get('/semilleros', [SemilleroController::class, 'index'])->name('semilleros.index');
//create
Route::get('/semilleros/create', [SemilleroController::class, 'create'])->name('semilleros.create');
Route::post('/semilleros', [SemilleroController::class, 'store'])->name('semilleros.store');

Route::get('/programa/{id}/profesores', [SemilleroController::class, 'getProfesoresPorPrograma']);

//edit
Route::get('/semilleros/{id}/edit', [SemilleroController::class, 'edit'])->name('semilleros.edit');
Route::put('/semilleros/{id}', [SemilleroController::class, 'update'])->name('semilleros.update');
//delete
Route::delete('/semilleros/{id}', [SemilleroController::class, 'delete'])->name('semilleros.delete');
 */