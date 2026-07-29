<?php

namespace App\Http\Requests\Unidade;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UnidadeCreateRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            "UNIDADE_NOME" => ["required", "string", "max:150", "unique:UNIDADE,UNIDADE_NOME",],
            "UNIDADE_SOLICITANTE" => ["required", "integer", "in:0,1",],
        ];
    }

    public function attributes()
    {
        return [
            "UNIDADE_ID" => "<b>UNIDADE ID</b>",
            "UNIDADE_NOME" => "<b>NOME DA UNIDADE</b>",
            "UNIDADE_SOLICITANTE" => "<b>UNIDADE SOLICITANTE</b>",
        ];
    }
}
