<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Validation\Rule;

class UsuarioUpdateRequest extends UsuarioCreateRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $uniqueIgnoreId = Rule::unique('USUARIO')->ignore($this->input('USUARIO_ID'), "USUARIO_ID");
        $contatoUniqueIgnoreId = Rule::unique('USUARIO', 'USUARIO_CONTATO')
            ->ignore($this->input('USUARIO_ID'), "USUARIO_ID");
        return [
            "USUARIO_ID" => ["required", "integer", "exists:USUARIO,USUARIO_ID"],
            "USUARIO_LOGIN" => ["required", $uniqueIgnoreId, "max:50"],
            "USUARIO_SENHA" => ["nullable", "required_with:USUARIO_SENHA_CONFIRMATION", "string", "same:USUARIO_SENHA_CONFIRMATION", "max:32"],
            "USUARIO_NOME" => ["required", "string", "max:255"],
            "USUARIO_CPF" => ["required", $uniqueIgnoreId, "cpf"],
            "USUARIO_CONTATO" => ["required", "regex:/^[0-9]{10,11}$/", $contatoUniqueIgnoreId],
            "USUARIO_ATIVO" => ["required", "integer"],
            "USUARIO_SENHA_CONFIRMATION" => ["nullable", "required_with:USUARIO_SENHA", "string"],
        ];
    }
}
