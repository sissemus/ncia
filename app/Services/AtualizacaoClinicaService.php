<?php

namespace App\Services;

use App\Models\AtualizacaoClinica;
use App\Models\Chamado;
use App\Models\ChamadoSituacao;
use App\MyLibs\SituacaoChamadoEnum;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AtualizacaoClinicaService
{
    public function registrar(Chamado $chamado, array $dados, $usuarioId)
    {
        $situacao = ChamadoSituacao::where("CHAMADO_ID", $chamado->CHAMADO_ID)
            ->orderByDesc("CHAMADO_SITUACAO_DATA")
            ->orderByDesc("CHAMADO_SITUACAO_ID")
            ->lockForUpdate()
            ->first();

        if (!$situacao || !in_array((int) $situacao->TG_SITUACAO_ID, [
            SituacaoChamadoEnum::ABERTO,
            SituacaoChamadoEnum::EM_ANALISE,
            SituacaoChamadoEnum::EM_ATENDIMENTO,
        ], true)) {
            throw ValidationException::withMessages([
                "CHAMADO_ID" => "O chamado foi concluído ou cancelado e não aceita atualização clínica.",
            ]);
        }

        $atualizacao = AtualizacaoClinica::create([
            "CHAMADO_ID" => $chamado->CHAMADO_ID,
            "TG_PRIORIDADE_ANTERIOR_ID" => $chamado->TG_PRIORIDADE_ID,
            "TG_PRIORIDADE_ID" => $dados["TG_PRIORIDADE_ID"],
            "TG_TIPO_PRECAUCAO_ID" => $dados["TG_TIPO_PRECAUCAO_ID"],
            "TG_SUPORTE_O2_ID" => $dados["TG_SUPORTE_O2_ID"],
            "TG_SUPORTE_HEMODINAMICO_ID" => $dados["TG_SUPORTE_HEMODINAMICO_ID"],
            "ATUALIZACAO_CLINICA_TEMPERATURA" => $dados["ATUALIZACAO_CLINICA_TEMPERATURA"],
            "ATUALIZACAO_CLINICA_PRESSAO_ARTERIAL" => $dados["ATUALIZACAO_CLINICA_PRESSAO_ARTERIAL"],
            "ATUALIZACAO_CLINICA_FREQUENCIA_CARDIACA" => $dados["ATUALIZACAO_CLINICA_FREQUENCIA_CARDIACA"],
            "ATUALIZACAO_CLINICA_SATURACAO_O2" => $dados["ATUALIZACAO_CLINICA_SATURACAO_O2"],
            "ATUALIZACAO_CLINICA_ESCALA_GLASGOW" => $dados["ATUALIZACAO_CLINICA_ESCALA_GLASGOW"] ?? null,
            "ATUALIZACAO_CLINICA_PROFISSIONAL" => $dados["ATUALIZACAO_CLINICA_PROFISSIONAL"],
            "ATUALIZACAO_CLINICA_CONSELHO" => $dados["ATUALIZACAO_CLINICA_CONSELHO"],
            "ATUALIZACAO_CLINICA_NUMERO_CONSELHO" => $dados["ATUALIZACAO_CLINICA_NUMERO_CONSELHO"],
            "ATUALIZACAO_CLINICA_JUSTIFICATIVA_MEDICA" => $dados["ATUALIZACAO_CLINICA_JUSTIFICATIVA_MEDICA"],
            "USUARIO_ID" => $usuarioId,
            "ATUALIZACAO_CLINICA_DATA" => Carbon::now("America/Sao_Paulo"),
        ]);

        $chamado->TG_PRIORIDADE_ID = $dados["TG_PRIORIDADE_ID"];
        $chamado->save();

        return $atualizacao->load("usuario");
    }
}
