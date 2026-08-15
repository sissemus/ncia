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
<<<<<<< HEAD
    // public function rules()
    // {
    //     return [
    //         "VEICULO_ID" => ["required"],
    //         "EQUIPE_TURNO" => ["required"],
    //     ];
    // }

    // public function attributes()
    // {
    //     return [
    //         "VEICULO_ID" => "<b>VEICULO_ID</b>",
    //         "EQUIPE_TURNO" => "<b>EQUIPE_TURNO</b>"
    //     ];
    // }
=======
    public function rules()
    {
        return [
            "VEICULO_ID" => ["required"],
            "PROFISSIONAL_ID" => ["required"],
        ];
    }

    public function attributes()
    {
        return [
            "VEICULO_ID" => "<b>VEICULO_ID</b>",
            "PROFISSIONAL_ID" => "<b>PROFISSIONAL_ID</b>",
        ];
    }
>>>>>>> b5ca06c (implementar abertura de chamado pela unidade)
}
