<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsuarioUnidade\UsuarioUnidadeCreateRequest;
use App\Models\Usuario;
use App\Models\UsuarioUnidade;
use Illuminate\Http\Request;

class UsuarioUnidadeController extends Controller
{
    public function inserir(UsuarioUnidadeCreateRequest $request)
    {
        $usuarioUnidade = new UsuarioUnidade($request->input());
        $usuarioUnidade->save();

        return response([
            "cod" => 1,
            "msg" => "Unidade vinculada com sucesso",
            "retorno" => Usuario::buscar($request->USUARIO_ID)
        ], 200);
    }

    public function listar(Request $request)
    {
        $usuarioUnidade = UsuarioUnidade::listar($request);

        return response([
            "cod" => 1,
            "msg" => "Unidades listadas com sucesso",
            "retorno" => $usuarioUnidade
        ], 200);
    }

    public function deletar(Request $request)
    {
        $usuarioUnidade = UsuarioUnidade::buscar($request->USUARIO_UNIDADE_ID);
        $usuarioUnidade->delete();

        return response([
            "cod" => 1,
            "msg" => "Unidade desvinculada com sucesso",
            "retorno" => Usuario::buscar($request->USUARIO_ID)
        ], 200);
    }
}
