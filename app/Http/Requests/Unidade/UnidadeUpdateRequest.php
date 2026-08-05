<?php

namespace App\Http\Requests\Unidade;

use Illuminate\Validation\Rule;

class UnidadeUpdateRequest extends UnidadeCreateRequest
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            "UNIDADE_ID" => ["required", "integer", "exists:UNIDADE,UNIDADE_ID"],
            "UNIDADE_NOME" => [
                "required",
                "string",
                "max:150",
                Rule::unique("UNIDADE", "UNIDADE_NOME")
                    ->ignore($this->UNIDADE_ID, "UNIDADE_ID")
            ],
        ]);
    }
}
