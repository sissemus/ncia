<?php

namespace App\Http\Controllers;

use App\Http\Requests\Usuario\UsuarioCreateRequest;
use App\Http\Requests\Usuario\UsuarioUpdateRequest;
use App\Models\Local;
use App\Models\Usuario;
use App\Models\UsuarioLocal;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller {
    public function __construct() {
        $this->middleware(["UsuarioAdmin"]);
    }

    public function view() {
        return view("usuario.usuario_view")
            ->with([
                "locais" => Local::listar()
            ]);
    }

    public function create(UsuarioCreateRequest $request) {
        try {
            DB::beginTransaction();
            $usuario = new Usuario($request->post());
            $usuario->save();
            $usuarioLocais = $request->post("usuarioLocais");
            if ($usuarioLocais) {
                foreach ($usuarioLocais as $usuarioLocalRow) {
                    $usuarioLocal = new UsuarioLocal($usuarioLocalRow);
                    $usuarioLocal->USUARIO_ID = $usuario->USUARIO_ID;
                    $usuarioLocal->save();
                }
            }
            DB::commit();
            return response(Usuario::getById($usuario->USUARIO_ID));
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function update(UsuarioUpdateRequest $request) {
        try {
            DB::beginTransaction();
            $usuario = Usuario::find($request->input("USUARIO_ID"));
            $senhaAtual = $usuario->USUARIO_SENHA;
            $senhaNova = $request->input("USUARIO_SENHA");
            $usuario->fill($request->input());
            if ($senhaNova != null) {
                $usuario->USUARIO_SENHA = $senhaNova;
            } else {
                $usuario->USUARIO_SENHA = $senhaAtual;
            }
            $usuario->update();
            UsuarioLocal::deleteByUsuarioId($usuario->USUARIO_ID);
            $usuarioLocais = $request->post("usuarioLocais");
            if ($usuarioLocais) {
                foreach ($usuarioLocais as $usuarioLocalRow) {
                    $usuarioLocal = new UsuarioLocal($usuarioLocalRow);
                    $usuarioLocal->USUARIO_ID = $usuario->USUARIO_ID;
                    $usuarioLocal->save();
                }
            }
            DB::commit();
            return response(Usuario::getById($usuario->USUARIO_ID));
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function listAll() {
        return response(Usuario::listAll());
    }
}
