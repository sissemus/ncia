<?php

namespace App\Http\Requests\AtualizacaoClinica;

use App\MyLibs\RTG;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AtualizacaoClinicaStoreRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    protected function prepareForValidation()
    {
        $this->merge([
            "ATUALIZACAO_CLINICA_PROFISSIONAL" => trim((string) $this->ATUALIZACAO_CLINICA_PROFISSIONAL),
            "ATUALIZACAO_CLINICA_CONSELHO" => strtoupper(trim((string) $this->ATUALIZACAO_CLINICA_CONSELHO)),
            "ATUALIZACAO_CLINICA_NUMERO_CONSELHO" => trim((string) $this->ATUALIZACAO_CLINICA_NUMERO_CONSELHO),
            "ATUALIZACAO_CLINICA_JUSTIFICATIVA_MEDICA" => trim((string) $this->ATUALIZACAO_CLINICA_JUSTIFICATIVA_MEDICA),
        ]);
    }

    public function rules()
    {
        return [
            "CHAMADO_ID" => ["required", "integer", "exists:CHAMADO,CHAMADO_ID"],
            "TG_PRIORIDADE_ID" => ["required", "integer", Rule::exists("TABELA_GENERICA", "COLUNA_ID")
                ->where("TABELA_ID", RTG::PRIORIDADE_PACIENTE)
                ->where("ATIVO", 1)
                ->where(function ($query) {
                    $query->where("DESCRICAO", "not like", "%AZUL%");
                })],
            "TG_TIPO_PRECAUCAO_ID" => ["required", "integer", Rule::exists("TABELA_GENERICA", "COLUNA_ID")->where("TABELA_ID", RTG::TIPO_PRECAUCAO)->where("ATIVO", 1)],
            "TG_SUPORTE_O2_ID" => ["required", "integer", Rule::exists("TABELA_GENERICA", "COLUNA_ID")->where("TABELA_ID", RTG::SUPORTE_O2)->where("ATIVO", 1)],
            "TG_SUPORTE_HEMODINAMICO_ID" => ["required", "integer", Rule::exists("TABELA_GENERICA", "COLUNA_ID")->where("TABELA_ID", RTG::SUPORTE_HEMODINAMICO)->where("ATIVO", 1)],
            "ATUALIZACAO_CLINICA_TEMPERATURA" => ["required", "string", "max:20"],
            "ATUALIZACAO_CLINICA_PRESSAO_ARTERIAL" => ["required", "string", "max:20"],
            "ATUALIZACAO_CLINICA_FREQUENCIA_CARDIACA" => ["required", "string", "max:20"],
            "ATUALIZACAO_CLINICA_SATURACAO_O2" => ["required", "string", "max:20"],
            "ATUALIZACAO_CLINICA_ESCALA_GLASGOW" => ["nullable", "string", "max:20"],
            "ATUALIZACAO_CLINICA_PROFISSIONAL" => ["required", "string", "max:150"],
            "ATUALIZACAO_CLINICA_CONSELHO" => ["required", Rule::in(["CRM", "COREN"])],
            "ATUALIZACAO_CLINICA_NUMERO_CONSELHO" => ["required", "regex:/^[0-9]{6}$/"],
            "ATUALIZACAO_CLINICA_JUSTIFICATIVA_MEDICA" => ["required", "string"],
        ];
    }

    public function attributes()
    {
        return [
            "TG_PRIORIDADE_ID" => "<b>PRIORIDADE</b>",
            "TG_TIPO_PRECAUCAO_ID" => "<b>TIPO DE PRECAUÇÃO</b>",
            "TG_SUPORTE_O2_ID" => "<b>SUPORTE O2</b>",
            "TG_SUPORTE_HEMODINAMICO_ID" => "<b>SUPORTE HEMODINÂMICO</b>",
            "ATUALIZACAO_CLINICA_TEMPERATURA" => "<b>TEMPERATURA</b>",
            "ATUALIZACAO_CLINICA_PRESSAO_ARTERIAL" => "<b>PRESSÃO ARTERIAL</b>",
            "ATUALIZACAO_CLINICA_FREQUENCIA_CARDIACA" => "<b>FREQUÊNCIA CARDÍACA</b>",
            "ATUALIZACAO_CLINICA_SATURACAO_O2" => "<b>SATURAÇÃO O2</b>",
            "ATUALIZACAO_CLINICA_PROFISSIONAL" => "<b>PROFISSIONAL RESPONSÁVEL</b>",
            "ATUALIZACAO_CLINICA_CONSELHO" => "<b>CONSELHO</b>",
            "ATUALIZACAO_CLINICA_NUMERO_CONSELHO" => "<b>NÚMERO DO CONSELHO</b>",
            "ATUALIZACAO_CLINICA_JUSTIFICATIVA_MEDICA" => "<b>JUSTIFICATIVA MÉDICA</b>",
        ];
    }
}
