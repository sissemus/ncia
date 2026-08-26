<?php

use App\Http\Controllers\AplicacaoController;
use App\Http\Controllers\ChamadoController;
use App\Http\Controllers\ProcedimentoController;
use App\Http\Controllers\DiagnosticoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ProfissionalController;
use App\Http\Controllers\TabelaGenericaController;
use App\Http\Controllers\UnidadeController;
use App\Http\Controllers\VeiculoController;
use App\Http\Controllers\VeiculoUnidadeController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\UsuarioPerfilController;
use App\Http\Controllers\UsuarioUnidadeController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\EquipeProfissionalController;
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

    Route::prefix('procedimento')->group(function () {
        Route::get('/', [ProcedimentoController::class, 'view']);
        Route::get('view', [ProcedimentoController::class, 'view']);
        Route::post('inserir', [ProcedimentoController::class, 'inserir']);
        Route::put('alterar', [ProcedimentoController::class, 'alterar']);
        Route::delete('deletar', [ProcedimentoController::class, 'deletar']);
        Route::get('listar', [ProcedimentoController::class, 'listar']);
        Route::post('pesquisar', [ProcedimentoController::class, 'pesquisar']);
        Route::get('buscar/{id}', [ProcedimentoController::class, 'buscar']);
        Route::get('search', [ProcedimentoController::class, 'search']);
    });

    Route::prefix('diagnostico')->group(function () {
        Route::get('/', [DiagnosticoController::class, 'view']);
        Route::get('view', [DiagnosticoController::class, 'view']);
        Route::post('inserir', [DiagnosticoController::class, 'inserir']);
        Route::put('alterar', [DiagnosticoController::class, 'alterar']);
        Route::delete('deletar', [DiagnosticoController::class, 'deletar']);
        Route::get('listar', [DiagnosticoController::class, 'listar']);
        Route::post('pesquisar', [DiagnosticoController::class, 'pesquisar']);
        Route::get('buscar/{id}', [DiagnosticoController::class, 'buscar']);
        Route::get('search', [DiagnosticoController::class, 'search']);
    });

    Route::prefix('unidade')->group(function () {
        Route::get('/', [UnidadeController::class, 'view']);
        Route::get('view', [UnidadeController::class, 'view']);
        Route::post('inserir', [UnidadeController::class, 'inserir']);
        Route::put('alterar', [UnidadeController::class, 'alterar']);
        Route::delete('deletar', [UnidadeController::class, 'deletar']);
        Route::get('listar', [UnidadeController::class, 'listar']);
        Route::post('pesquisar', [UnidadeController::class, 'pesquisar']);
        Route::get('buscar/{id}', [UnidadeController::class, 'buscar']);
        Route::get('search', [UnidadeController::class, 'search']);
    });

    Route::prefix('veiculo')->group(function () {
        Route::get('/', [VeiculoController::class, 'view']);
        Route::get('view', [VeiculoController::class, 'view']);
        Route::post('inserir', [VeiculoController::class, 'inserir']);
        Route::put('alterar', [VeiculoController::class, 'alterar']);
        Route::delete('deletar', [VeiculoController::class, 'deletar']);
        Route::get('listar', [VeiculoController::class, 'listar']);
        Route::post('pesquisar', [VeiculoController::class, 'pesquisar']);
        Route::get('buscar/{id}', [VeiculoController::class, 'buscar']);
        Route::get('search', [VeiculoController::class, 'search']);
        Route::put('alterar_situacao', [VeiculoController::class, 'alterarSituacao']);
    });

    Route::prefix('veiculo_unidade')->group(function () {
        Route::get('/', [VeiculoUnidadeController::class, 'view']);
        Route::get('view', [VeiculoUnidadeController::class, 'view']);
        Route::post('inserir', [VeiculoUnidadeController::class, 'inserir']);
        Route::put('alterar', [VeiculoUnidadeController::class, 'alterar']);
        Route::delete('deletar', [VeiculoUnidadeController::class, 'deletar']);
        Route::post('pesquisar', [VeiculoUnidadeController::class, 'pesquisar']);
        Route::get('buscar/{id}', [VeiculoUnidadeController::class, 'buscar']);
        Route::get('search', [VeiculoUnidadeController::class, 'search']);
        Route::post('desvincular', [VeiculoUnidadeController::class, 'desvincular']);
    });

    Route::prefix('usuario_unidade')->group(function () {
        Route::post('inserir', [UsuarioUnidadeController::class, 'inserir']);
        Route::get('listar/{usuarioId}', [UsuarioUnidadeController::class, 'listar']);
        Route::delete('deletar', [UsuarioUnidadeController::class, 'deletar']);
    });

    Route::prefix("profissional")->group(function () {
        Route::get("/", [ProfissionalController::class, "view"]);
        Route::get("view", [ProfissionalController::class, "view"]);
        Route::post("inserir", [ProfissionalController::class, "inserir"]);
        Route::put("alterar", [ProfissionalController::class, "alterar"]);
        Route::delete("deletar", [ProfissionalController::class, "deletar"]);
        Route::get("listar", [ProfissionalController::class, "listar"]);
        Route::post("pesquisar", [ProfissionalController::class, "pesquisar"]);
        Route::get("buscar/{id}", [ProfissionalController::class, "buscar"]);
        });

        Route::prefix("paciente")->group(function () {
        Route::get("/", [PacienteController::class, "view"]);
        Route::get("view", [PacienteController::class, "view"]);
        Route::post("inserir", [PacienteController::class, "inserir"]);
        Route::put("alterar", [PacienteController::class, "alterar"]);
        Route::get("listar", [PacienteController::class, "listar"]);
        Route::post("pesquisar", [PacienteController::class, "pesquisar"]);
        Route::get("buscar/{id}", [PacienteController::class, "buscar"]);
        Route::get("buscar-por-cpf", [PacienteController::class, "buscarPorCpf"]);
    });

    Route::prefix("chamado")->group(function () {
        Route::get("/", [ChamadoController::class, "view"]);
        Route::get("view", [ChamadoController::class, "view"]);
        Route::get("verificar-duplicidade", [ChamadoController::class, "verificarDuplicidade"]);
        Route::post("abrir", [ChamadoController::class, "abrir"]);
    });
    
    Route::prefix("equipe")->group(function () {
        Route::get("/", [EquipeController::class, "view"]);
        Route::get("view", [EquipeController::class, "view"]);
        Route::post("inserir", [EquipeController::class, "inserir"]);
        Route::put("alterar", [EquipeController::class, "alterar"]);
        Route::delete("deletar", [EquipeController::class, "deletar"]);
        Route::get("listar", [EquipeController::class, "listar"]);
        Route::post("pesquisar", [EquipeController::class, "pesquisar"]);
        Route::get("buscar/{id}", [EquipeController::class, "buscar"]);
        Route::get('search', [EquipeController::class, 'search']);
    });
    
    Route::prefix("equipeProfissional")->group(function () {
        Route::get("/", [EquipeProfissionalController::class, "view"]);
        Route::get("view", [EquipeProfissionalController::class, "view"]);
        Route::post("inserir", [EquipeProfissionalController::class, "inserir"]);
        Route::put("alterar", [EquipeProfissionalController::class, "alterar"]);
        Route::delete("deletar", [EquipeProfissionalController::class, "deletar"]);
        Route::get("listar", [EquipeProfissionalController::class, "listar"]);
        Route::post("pesquisar", [EquipeProfissionalController::class, "pesquisar"]);
        Route::get("buscar/{id}", [EquipeProfissionalController::class, "buscar"]);
        Route::get('search', [EquipeProfissionalController::class, 'search']);
        Route::get('searchNUsado', [EquipeProfissionalController::class, 'searchNUsado']);
    });
    
});
