<?php

namespace App\Http\Requests\Equipe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class EquipeCreateRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            "VEICULO_ID" => ["required", "integer"],
            "EQUIPE_TURNO" => ["required"],
            "equipeProfissionais" => ["required", "array", "min:1"]
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
