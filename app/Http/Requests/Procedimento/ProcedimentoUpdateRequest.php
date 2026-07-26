<?php

namespace App\Http\Requests\Procedimento;

use Illuminate\Validation\Rule;

class ProcedimentoUpdateRequest extends ProcedimentoCreateRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $uniqueIgnoreId = Rule::unique('PROCEDIMENTO')->ignore($this->request->all()["PROCEDIMENTO_ID"], "PROCEDIMENTO_ID");
        return [
            "PROCEDIMENTO_CODIGO" => ["required", "string", $uniqueIgnoreId, "max:20",],
            "PROCEDIMENTO_DESCRICAO" => ["required", "string", "min:3"],
            "PROCEDIMENTO_ATIVO" => ["required", "integer"],
        ];
    }
}
