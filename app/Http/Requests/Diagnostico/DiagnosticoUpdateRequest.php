<?php

namespace App\Http\Requests\Diagnostico;

use Illuminate\Validation\Rule;

class DiagnosticoUpdateRequest extends DiagnosticoCreateRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $uniqueIgnoreId = Rule::unique('DIAGNOSTICO')->ignore($this->request->all()["DIAGNOSTICO_ID"], "DIAGNOSTICO_ID");
        return [
            "DIAGNOSTICO_DESCRICAO" => ["required", "string", "min:3"],
            "DIAGNOSTICO_ATIVO" => ["required", "integer"],
        ];
    }
}
