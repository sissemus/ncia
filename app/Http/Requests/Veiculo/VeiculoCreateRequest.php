<?php

namespace App\Http\Requests\Veiculo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class VeiculoCreateRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            "VEICULO_IDENTIFICACAO" => ["required", "string", "max:150", "unique:VEICULO,VEICULO_IDENTIFICACAO"],
            "VEICULO_PLACA" => ["nullable", "string", "max:20"],
            "TG_TIPO_VEICULO_ID" => ["required", "integer"],
            "TG_SITUACAO_VEICULO_ID" => ["required", "integer"],
            "VEICULO_ATIVO" => ["required", "integer", "in:0,1"],
            "UNIDADE_ID" => ["nullable", "integer", "exists:UNIDADE,UNIDADE_ID"],
            "VEICULO_UNIDADE_DT_INI" => ["nullable", "date"],
        ];
    }

    public function attributes()
    {
        return [
            "VEICULO_ID" => "<b>VEÍCULO ID</b>",
            "VEICULO_IDENTIFICACAO" => "<b>IDENTIFICAÇÃO DO VEÍCULO</b>",
            "VEICULO_PLACA" => "<b>PLACA</b>",
            "TG_TIPO_VEICULO_ID" => "<b>TIPO DE VEÍCULO</b>",
            "TG_SITUACAO_VEICULO_ID" => "<b>SITUAÇÃO DO VEÍCULO</b>",
            "VEICULO_ATIVO" => "<b>ATIVO</b>",
        ];
    }
}
