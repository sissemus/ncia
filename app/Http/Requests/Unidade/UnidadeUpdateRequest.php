<?php

namespace App\Http\Requests\Unidade;

use Illuminate\Validation\Rule;

class UnidadeUpdateRequest extends UnidadeCreateRequest
{
    public function rules()
    {
        $uniqueIgnoreId = Rule::unique('UNIDADE', 'UNIDADE_NOME')->ignore($this->input("UNIDADE_ID"), "UNIDADE_ID");

        return [
            "UNIDADE_ID" => ["required", "integer", "exists:UNIDADE,UNIDADE_ID",],
            "UNIDADE_NOME" => ["required", "string", "max:150", $uniqueIgnoreId,],
            "UNIDADE_SOLICITANTE" => ["required", "integer", "in:0,1",],
        ];
    }
}
