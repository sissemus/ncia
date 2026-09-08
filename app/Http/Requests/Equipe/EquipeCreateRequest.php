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
        $rules = [];

        foreach($this->all() as $indice => $dados){

            $rules[$indice . '.VEICULO_ID'] = [
                'required', 'numeric',
            ];

            $rules[$indice . '.EQUIPE_TURNO'] =[
                'required'
            ];

            $rules[$indice . '.TG_TIPO_VEICULO_ID'] =[
                'required', 'numeric', 'in:1,2',
            ];

            $rules[$indice . '.TG_TIPO_PROFISSIONAL_ID'] =[
                'required', 'numeric',
            ];

            /**
             * TIPO VEICULO = 1, 2, ou 4
             * 1 = condutor
             * 2 = enfermeiro
             * 3 = medico
             * 4 = técnico em enfermagem
             * */
             if(($dados['TG_TIPO_VEICULO_ID'] ?? null) == 1){

                $rules[$indice . '.TG_TIPO_VEICULO_ID'][] = 'in:1,2,4';

             }

            /**
             * TIPO VEICULO = 1, 2, 3 ou 4
             * 
             * */
             if(($dados['TG_TIPO_VEICULO_ID'] ?? null) == 2){

                $rules[$indice . '.TG_TIPO_VEICULO_ID'][] = 'in:1,2,3,4';

             }

             
        }

        return $rules;

    }

    // public function withValidator(Validator $validator)
    // {
    //     $validator->after(function ($validator) {
    //         if (empty($this->all())) {
    //             $validator->errors()->add('geral', 'Dados incorretos para criar a equipe!');
    //         }
    //     });
    // }
    
        public function messages()
        {
            $messages = [];

            foreach ($this->all() as $indice => $dados) {
                // Mensagens para o VEICULO_ID
                $messages[$indice . '.VEICULO_ID.required'] = "O veículo do item " . ($indice + 1) . " é obrigatório.";
                $messages[$indice . '.VEICULO_ID.numeric'] = "O veículo do item " . ($indice + 1) . " deve ser um número.";

                // Mensagens para o EQUIPE_TURNO
                $messages[$indice . '.EQUIPE_TURNO.required'] = "O turno da equipe do item " . ($indice + 1) . " é obrigatório.";

                // Mensagens para o TG_TIPO_VEICULO_ID
                $messages[$indice . '.TG_TIPO_VEICULO_ID.required'] = "O tipo de veículo do item " . ($indice + 1) . " é obrigatório.";
                $messages[$indice . '.TG_TIPO_VEICULO_ID.numeric'] = "O tipo de veículo do item " . ($indice + 1) . " deve ser um número.";
                $messages[$indice . '.TG_TIPO_VEICULO_ID.in'] = "O tipo de veículo selecionado para o item " . ($indice + 1) . " é inválido.";

                // Mensagens para o TG_TIPO_PROFISSIONAL_ID
                $messages[$indice . '.TG_TIPO_PROFISSIONAL_ID.required'] = "O tipo de profissional do item " . ($indice + 1) . " é obrigatório.";
                $messages[$indice . '.TG_TIPO_PROFISSIONAL_ID.numeric'] = "O tipo de profissional do item " . ($indice + 1) . " deve ser um número.";
            }

            return $messages;
        }
}
