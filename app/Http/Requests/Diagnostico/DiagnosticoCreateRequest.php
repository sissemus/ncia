<?php

namespace App\Http\Requests\Diagnostico;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DiagnosticoCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "DIAGNOSTICO_DESCRICAO" => ["required", "string", "min:30"],
        ];
    }

    public function attributes()
    {
        return [
            "DIAGNOSTICO_ID" => "<b>DIAGNOSTICO ID</b>",
            "DIAGNOSTICO_DESCRICAO"  => "<b>DESCRIÇÃO</b>",
        ];
    }
}
