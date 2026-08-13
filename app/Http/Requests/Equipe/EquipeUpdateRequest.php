<?php

namespace App\Http\Requests\Equipe;

use Illuminate\Validation\Rule;

class EquipeUpdateRequest extends EquipeCreateRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    // public function rules()
    // {
    //     $uniqueIgnoreId = Rule::unique('EQUIPE')->ignore($this->request->all()["EQUIPE_ID"], "EQUIPE_ID");
    //     return [
    //         "VEICULO_ID" => ["required", "integer"],
    //         "PROFISSIONAL_ID" => ["required", "integer"],
    //         // "EQUIPE_DATA" => ["required", "date"],
    //         "EQUIPE_TURNO" => ["required", "string"],
    //         "EQUIPE_ATIVO" => ["required", "integer"],
    //     ];
    // }
}
