<?php

namespace App\Http\Requests\LocalSituacao;

use Illuminate\Foundation\Http\FormRequest;

class LocalSituacaoCreateRequest extends FormRequest {
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            "LOCAL_ID"              => ["required"],
            "SITUACAO_ID"           => ["required"],
        ];
    }

    public function attributes() {
        return [
            "LOCAL_ID"              => "<b>LOCAL DE VACINAÇÃO</b>",
            "SITUACAO_ID"           => "<b>STATUS DA FILA</b>",
        ];
    }
}
