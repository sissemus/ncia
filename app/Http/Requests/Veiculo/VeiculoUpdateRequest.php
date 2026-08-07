<?php

namespace App\Http\Requests\Veiculo;

use Illuminate\Validation\Rule;

class VeiculoUpdateRequest extends VeiculoCreateRequest
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            "VEICULO_ID" => ["required", "integer", "exists:VEICULO,VEICULO_ID"],
            "VEICULO_IDENTIFICACAO" => [
                "required",
                "string",
                "max:150",
                Rule::unique("VEICULO", "VEICULO_IDENTIFICACAO")
                    ->ignore($this->VEICULO_ID, "VEICULO_ID")
            ],
        ]);
    }
}
