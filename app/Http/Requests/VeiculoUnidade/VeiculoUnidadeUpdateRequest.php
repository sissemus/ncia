<?php

namespace App\Http\Requests\VeiculoUnidade;

class VeiculoUnidadeUpdateRequest extends VeiculoUnidadeCreateRequest
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            "VEICULO_UNIDADE_ID" => ["required", "integer", "exists:VEICULO_UNIDADE,VEICULO_UNIDADE_ID"],
        ]);
    }
}
