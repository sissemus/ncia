<?php

namespace App\Http\Requests\Profissional;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfissionalCreateRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'PROFISSIONAL_CPF' => preg_replace('/\D/', '', (string) $this->PROFISSIONAL_CPF),
            'PROFISSIONAL_WHATSAPP' => preg_replace('/\D/', '', (string) $this->PROFISSIONAL_WHATSAPP),
        ]);
    }

    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            "PROFISSIONAL_NOME" => ["required", "string", "max:150"],
            "PROFISSIONAL_CPF" => [
                "required",
                "cpf",
                Rule::unique("PROFISSIONAL", "PROFISSIONAL_CPF")
            ],
            "PROFISSIONAL_NASCIMENTO" => ["required", "date"],
            "TG_SEXO_ID" => [
                "required",
                "integer",
            ],
            "TG_TIPO_PROFISSIONAL_ID" => [
                "required",
                "integer",
            ],
            "PROFISSIONAL_WHATSAPP" => ["required", "regex:/^[0-9]{10,11}$/"],
            "PROFISSIONAL_ATIVO" => ["required", "integer", "in:0,1"],
            'CRIAR_ACESSO' => ['sometimes', 'boolean'],
            'PERFIL_ID' => ['nullable', 'integer'],
            'VINCULAR_USUARIO_ID' => ['nullable', 'integer'],
        ];
    }

    public function attributes()
    {
        return [
            "PROFISSIONAL_NOME" => "<b>NOME</b>",
            "PROFISSIONAL_CPF" => "<b>CPF</b>",
            "PROFISSIONAL_WHATSAPP" => "<b>CONTATO (WHATSAPP)</b>",
            "PROFISSIONAL_NASCIMENTO" => "<b>DATA DE NASCIMENTO</b>",
            "TG_SEXO_ID" => "<b>SEXO</b>",
            "TG_TIPO_PROFISSIONAL_ID" => "<b>TIPO DE PROFISSIONAL</b>",
            "PROFISSIONAL_ATIVO" => "<b>ATIVO</b>",
        ];
    }

    public function messages()
    {
        return ['PROFISSIONAL_WHATSAPP.regex' => 'O telefone de contato deve conter DDD e telefone com 10 ou 11 dígitos.'];
    }
}
