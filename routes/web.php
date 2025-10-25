<?php

use App\Http\Controllers\AplicacaoController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\TabelaGenericaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\UsuarioPerfilController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::middleware(['auth', 'web', 'CompartilharVariaveis'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::prefix('perfil')->group(function () {
        Route::get('/', [PerfilController::class, "view"]);
        Route::get('view', [PerfilController::class, "view"]);
        Route::post('create', [PerfilController::class, "create"]);
        Route::put('update', [PerfilController::class, "update"]);
        Route::delete('delete', [PerfilController::class, "delete"]);
        Route::get('list', [PerfilController::class, "list"]);
        Route::get('search', [PerfilController::class, "search"]);
    });

    Route::prefix('usuario')->group(function () {
        Route::get('/', [UsuarioController::class, "view"]);
        Route::get('view', [UsuarioController::class, "view"])->name("usuario.view");
        Route::get('alteracao_senha', [UsuarioController::class, "alterarSenhaView"])->name("usuario.alteracao_senha");
        Route::post('inserir', [UsuarioController::class, "inserir"]);
        Route::put('alterar', [UsuarioController::class, "alterar"]);
        Route::put('alterar_senha', [UsuarioController::class, "alterarSenha"])->name("usuario.alterar_senha");
        Route::delete('deletar', [UsuarioController::class, "deletar"]);
        Route::match(['get', 'post'], 'listar', [UsuarioController::class, "listar"]);
        Route::post('pesquisar', [UsuarioController::class, "pesquisar"]);
        Route::get('buscar/{id}', [UsuarioController::class, "buscar"]);
    });

    Route::prefix('usuario_perfil')->group(function () {
        Route::post('inserir', [UsuarioPerfilController::class, "inserir"]);
        Route::match(['get', 'post'], 'listar', [UsuarioPerfilController::class, "listar"]);
        Route::post('pesquisar', [UsuarioPerfilController::class, "pesquisar"]);
        Route::get('buscar/{id}', [UsuarioPerfilController::class, "buscar"]);
        Route::delete('deletar', [UsuarioPerfilController::class, "deletar"]);
        Route::put('alterar', [UsuarioPerfilController::class, "alterar"]);
    });

    Route::prefix('tabela_generica')->group(function () {
        Route::get('/', [TabelaGenericaController::class, "view"]);
        Route::get('view', [TabelaGenericaController::class, "view"]);
        Route::post('inserir', [TabelaGenericaController::class, "inserir"]);
        Route::get('listar', [TabelaGenericaController::class, "listar"]);
        Route::post('pesquisar', [TabelaGenericaController::class, "pesquisar"]);
        Route::get('buscar/{id}', [TabelaGenericaController::class, "buscar"]);
        Route::delete('deletar', [TabelaGenericaController::class, "deletar"]);
        Route::put('alterar', [TabelaGenericaController::class, "alterar"]);
        Route::get('carregar', [TabelaGenericaController::class, "carregar"]);

        Route::get('listar_colunas', [TabelaGenericaController::class, "listarColunas"]);
        Route::put('alterar_coluna', [TabelaGenericaController::class, "alterarColuna"]);
        Route::post('inserir_coluna', [TabelaGenericaController::class, "inserirColuna"]);
        Route::delete('remover_coluna', [TabelaGenericaController::class, "removerColuna"]);
        Route::post('inserir_tabela', [TabelaGenericaController::class, "inserirTabela"]);
        Route::put('alterar_tabela', [TabelaGenericaController::class, "alterarTabela"]);
    });

    Route::prefix('aplicacao')->group(function () {
        Route::get('/', [AplicacaoController::class, "view"]);
        Route::get('view', [AplicacaoController::class, "view"])->name('aplicacao.view');
        Route::get('search', [AplicacaoController::class, "search"]);
        Route::post('create', [AplicacaoController::class, "create"]);
        Route::delete('delete', [AplicacaoController::class, "delete"]);
        Route::put('update', [AplicacaoController::class, "update"]);
        Route::match(['get', 'post'], 'list', [AplicacaoController::class, "list"]);
    });

    Route::prefix('departamento')->group(function () {
        Route::get('/', [DepartamentoController::class, 'view']);
        Route::get('view', [DepartamentoController::class, 'view']);
        Route::post('inserir', [DepartamentoController::class, 'inserir']);
        Route::put('alterar', [DepartamentoController::class, 'alterar']);
        Route::delete('deletar', [DepartamentoController::class, 'deletar']);
        Route::get('listar', [DepartamentoController::class, 'listar']);
        Route::post('pesquisar', [DepartamentoController::class, 'pesquisar']);
        Route::get('buscar/{id}', [DepartamentoController::class, 'buscar']);
        Route::get('search', [DepartamentoController::class, 'search']);
    });
});
