<?php

namespace App\Http\Requests\dose;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DoseUpdateRequest extends FormRequest {
    public function authorize() {
        return Auth::check();
    }

    public function rules() {
        return [
            "DOSE_ID" => ["required"],
            "DOSE_NOME" => ["required", "max:30", "unique:DOSE,DOSE_NOME,{$this->input('DOSE_ID')},DOSE_ID"]
        ];
    }

    public function attributes() {
        return [
            "DOSE_NOME" => "<b>NOME DA DOSE</b>"
        ];
    }
}
