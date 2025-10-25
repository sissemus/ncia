<?php

namespace App\Http\Requests\Departamento;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DepartamentoCreateRequest extends FormRequest
{
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
            "HIERARQUIA_ID" => ["required", "integer"],
            "DEPARTAMENTO_NOME" => ["required", "string", "unique:DEPARTAMENTO", "max:100"],
            "DEPARTAMENTO_SIGLA" => ["required", "string", "unique:DEPARTAMENTO", "max:20",],
            "DEPARTAMENTO_DESCRICAO" => ["required", "string", "min:30"],
        ];
    }

    public function attributes()
    {
        return [
            "DEPARTAMENTO_ID" => "<b>DEPARTAMENTO ID</b>",
            "HIERARQUIA_ID" => "<b>HIERARQUIA</b>",
            "DEPARTAMENTO_NOME" => "<b>NOME</b>",
            "DEPARTAMENTO_SIGLA"  => "<b>SIGLA</b>",
            "DEPARTAMENTO_DESCRICAO"  => "<b>DESCRIÇÃO</b>",
        ];
    }
}
