<?php

use Illuminate\Support\Facades\Route;
use App\Modules\GestionDeSemilleros\Controllers\SemilleroController;
use App\Modules\GestionDeSemilleros\Controllers\CreateController;


Route::get('/', function () {
    return view('welcome');
});
