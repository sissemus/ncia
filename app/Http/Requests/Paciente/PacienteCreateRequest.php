<?php

namespace App\Http\Requests\Paciente;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PacienteCreateRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        $vulnerabilidadeSocial = (int) $this->PACIENTE_VULNERABILIDADE_SOCIAL === 1;

        return [
            "PACIENTE_VULNERABILIDADE_SOCIAL" => ["required", "integer", "in:0,1"],
            "PACIENTE_NOME" => [Rule::requiredIf(!$vulnerabilidadeSocial), "nullable", "string", "max:150"],
            "PACIENTE_CPF" => [
                Rule::requiredIf(!$vulnerabilidadeSocial),
                "nullable",
                "cpf",
                Rule::unique("PACIENTE", "PACIENTE_CPF")
            ],
            "PACIENTE_DT_NASCIMENTO" => [
                Rule::requiredIf(!$vulnerabilidadeSocial),
                "nullable",
                "date",
                "before_or_equal:today"
            ],
            "TG_SEXO_ID" => ["required", "integer"],
        ];
    }

    public function attributes()
    {
        return [
            "PACIENTE_ID" => "<b>PACIENTE ID</b>",
            "PACIENTE_CODIGO_TEMPORARIO" => "<b>CÓDIGO TEMPORÁRIO</b>",
            "PACIENTE_NOME" => "<b>NOME COMPLETO</b>",
            "PACIENTE_CPF" => "<b>CPF</b>",
            "PACIENTE_DT_NASCIMENTO" => "<b>DATA DE NASCIMENTO</b>",
            "TG_SEXO_ID" => "<b>SEXO AO NASCIMENTO</b>",
            "PACIENTE_VULNERABILIDADE_SOCIAL" => "<b>VULNERABILIDADE SOCIAL</b>",
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            "PACIENTE_CPF" => $this->PACIENTE_CPF ? preg_replace("/\D/", "", $this->PACIENTE_CPF) : null,
            "PACIENTE_VULNERABILIDADE_SOCIAL" => (int) $this->PACIENTE_VULNERABILIDADE_SOCIAL
        ]);
    }
}