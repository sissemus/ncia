<?php

namespace App\Http\Requests\vacina;

use Illuminate\Foundation\Http\FormRequest;

class VacinaUpdateRequest extends FormRequest {
    public function authorize() {
        return auth()->check();
    }

    public function rules() {
        return [
            "VACINA_ID"   => ["required"],
            "VACINA_NOME" => ["required", "max:20", "unique:VACINA,VACINA_NOME,{$this->input('VACINA_ID')},VACINA_ID"],
        ];
    }

    public function attributes() {
        return [
            "VACINA_NOME" => "<b>NOME DA VACINA</b>",
        ];
    }
}
