<?php

namespace App\Http\Requests\Equipe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

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
            '*.VEICULO_ID' => [
                'required',
                'numeric',
            ],
            '*.EQUIPE_TURNO' => [
                'required',
            ],
            '*.TG_TIPO_PROFISSIONAL_ID' => [
                'required',
            ],
            '*.PROFISSIONAL_ID' => [
                'required',
                'numeric',
            ],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if (empty($this->all())) {
                $validator->errors()->add('geral', 'Dados incorretos para criar a equipe!');
            }
        });
    }

    public function attributes()
    {
        return [
            '*.VEICULO_ID' =>
                '<b>VEICULO</b>',

            '*.EQUIPE_TURNO' =>
                '<b>TURNO</b>',

            '*.TG_TIPO_PROFISSIONAL_ID' =>
                '<b>TIPO DO PROFISSIONAL</b>',

            '*.PROFISSIONAL_ID' =>
                '<b>PROFISSIONAL</b>',
        ];
    }
}
