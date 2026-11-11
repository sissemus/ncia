<?php

namespace App\Http\Requests\Equipe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class EquipeCreateRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        if (is_string($this->input('EQUIPE_TURNO'))) {
            $this->merge(['EQUIPE_TURNO' => strtoupper(trim($this->input('EQUIPE_TURNO')))]);
        }
    }

    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            "VEICULO_ID" => ["required", "integer"],
            "EQUIPE_TURNO" => ["required", "string", "in:SD,SN"],
            "equipeProfissionais" => ["required", "array", "min:1"],
            "equipeProfissionais.*.PROFISSIONAL_ID" => ["required", "integer", "distinct"],
        ];
    }

    public function attributes()
    {
        return [
            "VEICULO_ID" => "<b>VEÍCULO</b>",
            "EQUIPE_TURNO" => "<b>TURNO</b>",
            "equipeProfissionais" => "<b>PROFISSIONAIS SELECIONADOS</b>",
        ];
    }
}
