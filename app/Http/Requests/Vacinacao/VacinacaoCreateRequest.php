<?php

namespace App\Http\Requests\Vacinacao;

use Illuminate\Foundation\Http\FormRequest;

class VacinacaoCreateRequest extends FormRequest {
    public function authorize() {
        return auth()->check();
    }

    public function rules() {
        return [
//            "VACINACAO_ID" => null
//            "VACINACAO_DH" => ["required"],
            "VACINA_LOCAL_ID"=> ["required", "numeric"],
            "DOSE_ID"        => ["required", "numeric"],
            "VACINACAO_QTD"  => ["required", "numeric"],
        ];
    }

    public function attributes() {
        return [
            "VACINA_LOCAL_ID" => "<b>VACINA</b>",
            "VACINACAO_QTD"   => "<b>QUANTIDADE</b>",
            "DOSE_ID"         => "<b>DOSE</b>",
        ];
    }
}
