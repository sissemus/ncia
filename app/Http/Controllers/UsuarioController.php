<?php

namespace App\Http\Controllers;

use App\Http\Requests\Usuario\UsuarioCreateRequest;
use App\Http\Requests\Usuario\UsuarioUpdatePasswordRequest;
use App\Http\Requests\Usuario\UsuarioUpdateRequest;
use App\Models\Usuario;
use App\Services\IntegracaoProfissionalUsuarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    public function view()
    {
        return view('usuario.usuario_view');
    }

    public function alteracaoSenha()
    {
        // $usuario = Auth::user(); 
        return view('usuario.alteracao_senha');
    }

    public function inserir(UsuarioCreateRequest $request)
    {
        $usuario = new Usuario($request->input());
        $usuario->USUARIO_ATIVO = 1;
        $usuario->USUARIO_SENHA = md5($request->USUARIO_SENHA);
        $usuario->save();

        return response([
            "cod" => 1,
            "msg" => "Usuário adicionado com sucesso",
            "retorno" => Usuario::buscar($usuario->USUARIO_ID)
        ], 200);
    }

    public function listar(Request $request)
    {
        // dd($request->input());
        $usuario = Usuario::listar($request)->paginate();

        return response([
            "cod" => 1,
            "msg" => "Usuário listado com sucesso",
            "retorno" => $usuario
        ], 200);
    }

    public function buscar($id)
    {
        $usuario = Usuario::buscar($id);

        return response([
            "cod" => 1,
            "msg" => "Usuário id {$id} buscado com sucesso",
            "retorno" => $usuario
        ], 200);
    }


    public function alterar(UsuarioUpdateRequest $request, IntegracaoProfissionalUsuarioService $integracao)
    {
        $usuario = $integracao->atualizarUsuario($request->validated());

        return response([
            "cod" => 1,
            "msg" => "Usuário id {$request->USUARIO_ID} alterado com sucesso",
            "retorno" => $usuario
        ], 200);
    }

    public function alterarSenha(UsuarioUpdatePasswordRequest $request)
    {
        $usuario = Usuario::buscar(Auth::id());
        $usuario->USUARIO_SENHA = md5($request->USUARIO_SENHA);
        $usuario->USUARIO_ULTIMO_ACESSO = now();
        $usuario->update();

        return response([
            "cod" => 1,
            "msg" => "Senha alterada com sucesso",
            "retorno" => $usuario
        ], 200);
    }
}
