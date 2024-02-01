<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;

class UsuarioUpdateRequest extends FormRequest {
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            "USUARIO_ID"    => ["required"],
            "USUARIO_LOGIN" => ["required"],
//            "USUARIO_SENHA" => ["required"],
            "USUARIO_ATIVO" => ["required"],
            "USUARIO_ADM"   => ["required"],
            "usuarioLocais" => ["required"],
        ];
    }

    public function attributes() {
        return [
            "USUARIO_ID"    => "<b>ID DO USUÁRIO</b>",
            "USUARIO_LOGIN" => "<b>LOGIN</b>",
            "USUARIO_SENHA" => "<b>SENHA</b>",
            "USUARIO_ATIVO" => "<b>ATIVO</b>",
            "USUARIO_ADM"   => "<b>ADMINISTRADOR</b>",
            "usuarioLocais" => "<b>LOCAL</b>",
        ];
    }
}
