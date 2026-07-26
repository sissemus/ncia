<?php

namespace App\Http\Requests\Procedimento;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProcedimentoCreateRequest extends FormRequest
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
            "PROCEDIMENTO_CODIGO" => ["required"],
            "PROCEDIMENTO_DESCRICAO" => ["required"],
        ];
    }

    public function attributes()
    {
        return [
            "PROCEDIMENTO_CODIGO" => "<b>PROCEDIMENTO_CODIGO</b>",
            "PROCEDIMENTO_DESCRICAO" => "<b>PROCEDIMENTO_DESCRICAO</b>",
        ];
    }
}
