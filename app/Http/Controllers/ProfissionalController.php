<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profissional\ProfissionalCreateRequest;
use App\Http\Requests\Profissional\ProfissionalUpdateRequest;
use App\Models\Profissional;
use App\Models\TabelaGenerica;
use App\Services\IntegracaoProfissionalUsuarioService;
use Illuminate\Http\Request;

class ProfissionalController extends Controller
{
    private $integracao;

    public function __construct(IntegracaoProfissionalUsuarioService $integracao)
    {
        $this->integracao = $integracao;
    }

    public function acessoOpcoes(Request $request)
    {
        return response([
            'cod' => 1,
            'msg' => 'Opções de acesso carregadas',
            'retorno' => [
                'podeGerenciarAcesso' => $this->integracao->podeGerenciarAcesso($request->user()),
                'perfis' => $this->integracao->perfisPermitidos($request->user())->orderBy('PERFIL_NOME')->get(),
            ],
        ]);
    }

    public function usuarioPorCpf(Request $request)
    {
        abort_unless($this->integracao->podeGerenciarAcesso($request->user()), 403);
        $cpf = preg_replace('/\D/', '', (string) $request->input('CPF'));
        validator(['CPF' => $cpf], ['CPF' => ['required', 'cpf']])->validate();
        $usuario = $this->integracao->usuarioPorCpf($cpf);
        return response(['cod' => 1, 'msg' => 'Consulta concluída', 'retorno' => $usuario ? [
            'USUARIO_ID' => $usuario->USUARIO_ID,
            'USUARIO_NOME' => $usuario->USUARIO_NOME,
            'USUARIO_LOGIN' => $usuario->USUARIO_LOGIN,
            'USUARIO_ATIVO' => $usuario->USUARIO_ATIVO,
            'vinculado' => $usuario->profissional()->exists(),
        ] : null]);
    }

    public function view()
    {   
        $sexos = TabelaGenerica::sexo();
        $tiposProfissional = TabelaGenerica::tipoProfissional();
        return view("profissional.profissional_view", compact('sexos', 'tiposProfissional'));
    }

    public function inserir(ProfissionalCreateRequest $request)
    {
        $resultado = $this->integracao->salvarProfissional($request->validated(), $request->user());

        return response([
            "cod" => 1,
            "msg" => "Profissional cadastrado com sucesso",
            "retorno" => $resultado['profissional'],
            'usuario_criado' => $resultado['usuario_criado'],
        ], 200);
    }

    public function alterar(ProfissionalUpdateRequest $request)
    {
        $resultado = $this->integracao->salvarProfissional($request->validated(), $request->user());

        return response([
            "cod" => 1,
            "msg" => "Profissional alterado com sucesso",
            "retorno" => $resultado['profissional'],
            'usuario_criado' => $resultado['usuario_criado'],
        ], 200);
    }

    public function buscar($id)
    {
        return response([
            "cod" => 1,
            "msg" => "Profissional encontrado com sucesso",
            "retorno" => Profissional::buscar($id)
        ], 200);
    }

    public function listar(Request $request)
    {
        $profissionais = Profissional::listar($request)->get();

        return response([
            "cod" => 1,
            "msg" => "Profissionais listados com sucesso",
            "retorno" => $profissionais
        ], 200);
    }

    public function pesquisar(Request $request)
    {
        $profissionais = Profissional::listar($request)
            ->paginate(15);

        return response([
            "cod" => 1,
            "msg" => "Profissionais pesquisados com sucesso",
            "retorno" => $profissionais
        ], 200);
    }

    public function pesquisarNEquipe(Request $request)
    {
        $profissionais = Profissional::listarNPesquisa($request)
            ->get();

        return response([
            "cod" => 1,
            "msg" => "Profissionais pesquisados com sucesso",
            "retorno" => $profissionais
        ], 200);
    }

    public function deletar(Request $request)
    {
        $profissional = Profissional::buscar($request->PROFISSIONAL_ID);
        $profissional->PROFISSIONAL_ATIVO = 0;
        $profissional->update();

        return response([
            "cod" => 1,
            "msg" => "Profissional inativado com sucesso",
            "retorno" => Profissional::buscar($profissional->PROFISSIONAL_ID)
        ], 200);
    }
}
