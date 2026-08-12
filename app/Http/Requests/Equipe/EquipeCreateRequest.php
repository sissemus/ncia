<?php

namespace App\Http\Requests\Equipe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class EquipeCreateRequest extends FormRequest
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
            "VEICULO_ID" => ["required"],
            "PROFISSIONAL_ID" => ["required"],
            "EQUIPE_DATA_INI" => ["required", "date"],
            "EQUIPE_DATA_FIM" => ["required", "date"],
        ];
    }

    public function attributes()
    {
        return [
            "VEICULO_ID" => "<b>VEICULO_ID</b>",
            "PROFISSIONAL_ID" => "<b>PROFISSIONAL_ID</b>",
            "EQUIPE_DATA_INI" => "<b>EQUIPE_DATA_INI</b>",
            "EQUIPE_DATA_FIM" => "<b>EQUIPE_DATA_FIM</b>",
        ];
    }
}
