<?php

namespace App\Http\Requests\Profissional;

use Illuminate\Validation\Rule;

class ProfissionalUpdateRequest extends ProfissionalCreateRequest
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            "PROFISSIONAL_ID" => [
                "required",
                "integer",
                "exists:PROFISSIONAL,PROFISSIONAL_ID"
            ],
            "PROFISSIONAL_CPF" => [
                "required",
                "cpf",
                Rule::unique("PROFISSIONAL", "PROFISSIONAL_CPF")
                    ->ignore($this->PROFISSIONAL_ID, "PROFISSIONAL_ID")
            ],
        ]);
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), [
            "PROFISSIONAL_ID" => "<b>PROFISSIONAL ID</b>",
        ]);
    }
}