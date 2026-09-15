<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UsuarioCreateRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            "USUARIO_CONTATO" => preg_replace('/[^0-9]/', '', (string) $this->USUARIO_CONTATO),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "USUARIO_LOGIN" => ["required", "unique:USUARIO", "max:50"],
            "USUARIO_SENHA" => ["required", "same:USUARIO_SENHA_CONFIRMATION", "max:32"],
            "USUARIO_NOME" => ["required", "unique:USUARIO", "max:255"],
            "USUARIO_CPF" => ["required", "unique:USUARIO", "cpf"],
            "USUARIO_CONTATO" => ["required", "regex:/^[0-9]{10,11}$/", "unique:USUARIO,USUARIO_CONTATO"],
            "USUARIO_ATIVO" => ["required", "integer"],
            "USUARIO_SENHA_CONFIRMATION" => ["required"]
        ];
    }

    public function attributes()
    {
        return [
            "USUARIO_ID" => "<b>USUARIO ID</b>",
            "USUARIO_LOGIN" => "<b>LOGIN</b>",
            "USUARIO_SENHA" => "<b>SENHA</b>",
            "USUARIO_NOME"  => "<b>NOME</b>",
            "USUARIO_CPF"  => "<b>CPF</b>",
            "USUARIO_CONTATO" => "<b>NÚMERO DE CONTATO</b>",
            "USUARIO_ATIVO" => "<b>ATIVO</b>",
            "USUARIO_SENHA_CONFIRMATION" => "<b>CONFIRMAR SENHA</b>",
        ];
    }

    public function messages()
    {
        return [
            "USUARIO_CPF.cpf" => 'O campo :attribute é inválido.',
            "USUARIO_CONTATO.regex" => 'O campo :attribute deve conter DDD e telefone com 10 ou 11 dígitos.'
        ];
    }
}
