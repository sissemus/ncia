<?php

namespace App\Http\Requests\vacina;

use Illuminate\Foundation\Http\FormRequest;

class VacinaCreateRequest extends FormRequest {
    public function authorize() {
        return auth()->check();
    }

    public function rules() {
        return [
            "VACINA_NOME" => ["required", "max:20", "unique:VACINA,VACINA_NOME"],
        ];
    }

    public function attributes() {
        return [
            "VACINA_NOME" => "<b>NOME DA VACINA</b>",
        ];
    }
}
