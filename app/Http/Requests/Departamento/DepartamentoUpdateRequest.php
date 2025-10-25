<?php

namespace App\Http\Requests\Departamento;

use Illuminate\Validation\Rule;

class DepartamentoUpdateRequest extends DepartamentoCreateRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $uniqueIgnoreId = Rule::unique('DEPARTAMENTO')->ignore($this->request->all()["DEPARTAMENTO_ID"], "DEPARTAMENTO_ID");
        return [
            "HIERARQUIA_ID" => ["required", "integer"],
            "DEPARTAMENTO_NOME" => ["required", "string", $uniqueIgnoreId, "max:100"],
            "DEPARTAMENTO_SIGLA" => ["required", "string", $uniqueIgnoreId, "max:20",],
            "DEPARTAMENTO_DESCRICAO" => ["required", "string", "min:30"],
            "DEPARTAMENTO_ATIVO" => ["required", "integer"],
        ];
    }
}
