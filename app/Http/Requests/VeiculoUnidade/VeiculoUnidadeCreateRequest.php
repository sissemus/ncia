<?php

namespace App\Http\Requests\VeiculoUnidade;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class VeiculoUnidadeCreateRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            "VEICULO_ID" => ["required", "integer", "exists:VEICULO,VEICULO_ID"],
            "UNIDADE_ID" => ["required", "integer", "exists:UNIDADE,UNIDADE_ID"],
        ];
    }

    public function attributes()
    {
        return [
            "VEICULO_UNIDADE_ID" => "<b>ID DO VÍNCULO</b>",
            "VEICULO_ID" => "<b>VEÍCULO</b>",
            "UNIDADE_ID" => "<b>UNIDADE DE SAÚDE</b>",
            "VEICULO_UNIDADE_DT_INI" => "<b>DATA INÍCIO</b>",
            "VEICULO_UNIDADE_DT_FIM" => "<b>DATA FIM</b>",
        ];
    }
}
