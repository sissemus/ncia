<?php

namespace App\Http\Requests\Paciente;

use App\Models\Paciente;
use Illuminate\Validation\Rule;

class PacienteUpdateRequest extends PacienteCreateRequest
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            "PACIENTE_ID" => ["required", "integer", "exists:PACIENTE,PACIENTE_ID"],
            "PACIENTE_CPF" => ["nullable", "cpf", Rule::unique("PACIENTE", "PACIENTE_CPF")->ignore($this->PACIENTE_ID, "PACIENTE_ID")],
        ]);
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ["PACIENTE_ID" => "<b>PACIENTE ID</b>"]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->PACIENTE_ID) return;

            $paciente = Paciente::find($this->PACIENTE_ID);
            if (!$paciente) return;

            $cpfAtual = preg_replace("/\D/", "", $paciente->PACIENTE_CPF);
            $cpfInformado = $this->PACIENTE_CPF ? preg_replace("/\D/", "", $this->PACIENTE_CPF) : null;

            if ($cpfAtual && $cpfAtual !== $cpfInformado) $validator->errors()->add("PACIENTE_CPF", "O CPF do paciente não pode ser alterado ou removido.");
        });
    }
}
