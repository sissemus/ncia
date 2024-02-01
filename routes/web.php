<?php

use App\Http\Controllers\DoseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\LocalSituacaoController;
use App\Http\Controllers\PublicoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VacinacaoController;
use App\Http\Controllers\VacinaController;
use App\Http\Controllers\VacinaLocalController;
use App\Models\Local;
use App\Models\LocalSituacao;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class,'publico']);

Route::get('/login', function () {
    return view('auth.login');
});

Auth::routes();

Route::middleware(['auth', 'web', 'CompartilharVariaveis'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

        Route::prefix("usuario")->group(function () {
        Route::get("view", [UsuarioController::class, "view"]);
        Route::post("create", [UsuarioController::class, "create"]);
        Route::put("update", [UsuarioController::class, "update"]);
        Route::get("list-all", [UsuarioController::class, "ListAll"]);
    });
    Route::prefix("publico")->group(function () {
        Route::get("listar/local", [PublicoController::class, "getByLocalId"]);
    });
});
