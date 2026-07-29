<?php

namespace App\Http\Requests\UsuarioUnidade;

use App\Rules\Usuario\UnicoUsuarioUnidade;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UsuarioUnidadeCreateRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            "USUARIO_ID" => [
                "required",
                "integer",
                "exists:USUARIO,USUARIO_ID",
            ],
            "UNIDADE_ID" => [
                "required",
                "integer",
                "exists:UNIDADE,UNIDADE_ID",
                new UnicoUsuarioUnidade($this->request->all()['USUARIO_ID'])
            ],
        ];
    }

    public function attributes()
    {
        return [
            "USUARIO_ID" => "<b>USUÁRIO</b>",
            "UNIDADE_ID" => "<b>UNIDADE</b>",
        ];
    }
}