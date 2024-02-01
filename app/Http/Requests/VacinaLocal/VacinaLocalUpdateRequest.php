<?php

namespace App\Http\Requests\VacinaLocal;

use Illuminate\Foundation\Http\FormRequest;

class VacinaLocalUpdateRequest extends FormRequest {
    public function authorize() {
        return auth()->check();
    }

    public function rules() {
        return [
            "VACINA_LOCAL_ID" => ["required"],
            "VACINA_ID" => ["required", "integer"],
            "LOCAL_ID" => ["required", "integer"],
            "VACINA_LOCAL_QTD" => ["required", "integer"]
        ];
    }

    public function attributes() {
        return [
            "VACINA_LOCAL_ID" => "<b>VACINA ID</b>",
            "VACINA_ID" => "<b>VACINA</b>",
            "LOCAL_ID" => "<b>LOCAL</b>",
            "VACINA_LOCAL_QTD" => "<b>QUANTIDADE</b>",
        ];
    }
}
