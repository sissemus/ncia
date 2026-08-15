<?php

namespace App\Http\Requests\Chamado;

use App\MyLibs\RTG;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AbrirChamadoRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            "PACIENTE_VULNERABILIDADE_SOCIAL" => ["required", "boolean"],
            "PACIENTE_ID" => [
                "nullable",
                "integer",
                "required_if:PACIENTE_VULNERABILIDADE_SOCIAL,0",
                "prohibited_if:PACIENTE_VULNERABILIDADE_SOCIAL,1",
                Rule::exists("PACIENTE", "PACIENTE_ID")->where("PACIENTE_TEMPORARIO", 0),
            ],
            "PACIENTE_NOME" => ["nullable", "string", "max:150"],
            "PACIENTE_DT_NASCIMENTO" => ["nullable", "date"],
            "TG_SEXO_ID" => [
                "nullable",
                "integer",
                "required_if:PACIENTE_VULNERABILIDADE_SOCIAL,1",
                Rule::exists("TABELA_GENERICA", "COLUNA_ID")->where("TABELA_ID", RTG::SEXO)->where("ATIVO", 1),
            ],

            "TG_CHAMADO_ID" => ["required", "integer", Rule::exists("TABELA_GENERICA", "COLUNA_ID")->where("TABELA_ID", RTG::TIPO_CHAMADO)->where("ATIVO", 1)],
            "TG_PRIORIDADE_ID" => ["required", "integer", Rule::exists("TABELA_GENERICA", "COLUNA_ID")->where("TABELA_ID", RTG::PRIORIDADE_PACIENTE)->where("ATIVO", 1)],

            "UNIDADE_ID_SOLICITANTE" => [
                "required",
                "integer",
                Rule::exists("UNIDADE", "UNIDADE_ID")->where("UNIDADE_SOLICITANTE", 1)->where("UNIDADE_ATIVO", 1),
                Rule::exists("USUARIO_UNIDADE", "UNIDADE_ID")->where("USUARIO_ID", Auth::id()),
            ],
            "UNIDADE_ID_DESTINO" => ["required", "integer", Rule::exists("UNIDADE", "UNIDADE_ID")->where("UNIDADE_ATIVO", 1)],
            "PROFISSIONAL_ID_SOLICITANTE" => ["required", "integer", Rule::exists("PROFISSIONAL", "PROFISSIONAL_ID")->where("PROFISSIONAL_ATIVO", 1)],

            "CHAMADO_HORARIO_ATENDIMENTO" => ["nullable", "date_format:H:i"],
            "CHAMADO_SETOR_SOLICITANTE" => ["nullable", "string", "max:150"],
            "CHAMADO_LEITO_SOLICITANTE" => ["nullable", "string", "max:50"],
            "CHAMADO_SETOR_DESTINO" => ["nullable", "string", "max:150"],
            "CHAMADO_LEITO_DESTINO" => ["nullable", "string", "max:50"],

            "PROCEDIMENTO_ID" => ["required", "integer", Rule::exists("PROCEDIMENTO", "PROCEDIMENTO_ID")->where("PROCEDIMENTO_ATIVO", 1)],
            "DIAGNOSTICO_ID" => ["nullable", "integer", Rule::exists("DIAGNOSTICO", "DIAGNOSTICO_ID")->where("DIAGNOSTICO_ATIVO", 1)],

            "CHAMADO_DISPOSITIVOS" => ["nullable", "string", "max:500"],
            "CHAMADO_PESO" => ["nullable", "numeric", "min:0", "max:9999.99"],

            "TG_TIPO_PRECAUCAO_ID" => ["nullable", "integer", Rule::exists("TABELA_GENERICA", "COLUNA_ID")->where("TABELA_ID", RTG::TIPO_PRECAUCAO)->where("ATIVO", 1)],
            "TG_SUPORTE_O2_ID" => ["nullable", "integer", Rule::exists("TABELA_GENERICA", "COLUNA_ID")->where("TABELA_ID", RTG::SUPORTE_O2)->where("ATIVO", 1)],
            "TG_SUPORTE_HEMODINAMICO_ID" => ["nullable", "integer", Rule::exists("TABELA_GENERICA", "COLUNA_ID")->where("TABELA_ID", RTG::SUPORTE_HEMODINAMICO)->where("ATIVO", 1)],
            "TG_TEMPERATURA_ID" => ["nullable", "integer", Rule::exists("TABELA_GENERICA", "COLUNA_ID")->where("TABELA_ID", RTG::SINAIS_VITAIS_TEMPERATURA)->where("ATIVO", 1)],
            "TG_FREQUENCIA_CARDIACA_ID" => ["nullable", "integer", Rule::exists("TABELA_GENERICA", "COLUNA_ID")->where("TABELA_ID", RTG::SINAIS_VITAIS_FREQUENCIA_CARDIACA)->where("ATIVO", 1)],
            "TG_PRESSAO_ARTERIAL_ID" => ["nullable", "integer", Rule::exists("TABELA_GENERICA", "COLUNA_ID")->where("TABELA_ID", RTG::SINAIS_VITAIS_PRESSAO_ARTERIAL)->where("ATIVO", 1)],
            "TG_SATURACAO_ID" => ["nullable", "integer", Rule::exists("TABELA_GENERICA", "COLUNA_ID")->where("TABELA_ID", RTG::SINAIS_VITAIS_SATURACAO)->where("ATIVO", 1)],

            "CHAMADO_OBSERVACAO" => ["nullable", "string"],
        ];
    }

    public function attributes()
    {
        return [
            "PACIENTE_VULNERABILIDADE_SOCIAL" => "<b>VULNERABILIDADE SOCIAL</b>",
            "PACIENTE_ID" => "<b>PACIENTE</b>",
            "PACIENTE_NOME" => "<b>NOME DO PACIENTE</b>",
            "PACIENTE_DT_NASCIMENTO" => "<b>DATA DE NASCIMENTO</b>",
            "TG_SEXO_ID" => "<b>SEXO</b>",
            "TG_CHAMADO_ID" => "<b>TIPO DE CHAMADO</b>",
            "TG_PRIORIDADE_ID" => "<b>PRIORIDADE</b>",
            "UNIDADE_ID_SOLICITANTE" => "<b>UNIDADE SOLICITANTE</b>",
            "UNIDADE_ID_DESTINO" => "<b>UNIDADE DESTINO</b>",
            "PROFISSIONAL_ID_SOLICITANTE" => "<b>PROFISSIONAL SOLICITANTE</b>",
            "CHAMADO_HORARIO_ATENDIMENTO" => "<b>HORÁRIO DE ATENDIMENTO</b>",
            "CHAMADO_SETOR_SOLICITANTE" => "<b>SETOR SOLICITANTE</b>",
            "CHAMADO_LEITO_SOLICITANTE" => "<b>LEITO SOLICITANTE</b>",
            "CHAMADO_SETOR_DESTINO" => "<b>SETOR DESTINO</b>",
            "CHAMADO_LEITO_DESTINO" => "<b>LEITO DESTINO</b>",
            "PROCEDIMENTO_ID" => "<b>PROCEDIMENTO</b>",
            "DIAGNOSTICO_ID" => "<b>DIAGNÓSTICO</b>",
            "CHAMADO_DISPOSITIVOS" => "<b>DISPOSITIVOS</b>",
            "CHAMADO_PESO" => "<b>PESO</b>",
            "TG_TIPO_PRECAUCAO_ID" => "<b>TIPO DE PRECAUÇÃO</b>",
            "TG_SUPORTE_O2_ID" => "<b>SUPORTE O2</b>",
            "TG_SUPORTE_HEMODINAMICO_ID" => "<b>SUPORTE HEMODINÂMICO</b>",
            "TG_TEMPERATURA_ID" => "<b>TEMPERATURA</b>",
            "TG_FREQUENCIA_CARDIACA_ID" => "<b>FREQUÊNCIA CARDÍACA</b>",
            "TG_PRESSAO_ARTERIAL_ID" => "<b>PRESSÃO ARTERIAL</b>",
            "TG_SATURACAO_ID" => "<b>SATURAÇÃO</b>",
            "CHAMADO_OBSERVACAO" => "<b>OBSERVAÇÃO</b>",
        ];
    }
}
