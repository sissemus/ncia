<?php

namespace App\Http\Controllers;

use App\Http\Requests\AtualizacaoClinica\AtualizacaoClinicaStoreRequest;
use App\Models\AtualizacaoClinica;
use App\Models\Chamado;
use App\Models\Profissional;
use App\MyLibs\PerfilEnum;
use App\MyLibs\SituacaoChamadoEnum;
use App\Services\AtualizacaoClinicaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AtualizacaoClinicaController extends Controller
{
    private $service;

    public function __construct(AtualizacaoClinicaService $service)
    {
        $this->middleware("auth");
        $this->service = $service;
    }

    public function chamado($id)
    {
        $chamado = Chamado::with("situacaoAtual")->findOrFail($id);
        $perfis = $this->perfisAtivos();
        $this->autorizarConsulta($chamado, $perfis);

        return response([
            "atualizacoes" => $this->atualizacoes($chamado->CHAMADO_ID),
            "valoresAtuais" => $this->valoresAtuais($chamado),
            "podeCriar" => $this->podeCriar($chamado, $perfis),
        ]);
    }

    public function store(AtualizacaoClinicaStoreRequest $request)
    {
        $perfis = $this->perfisAtivos();

        return DB::transaction(function () use ($request, $perfis) {
            $chamado = Chamado::lockForUpdate()->findOrFail($request->CHAMADO_ID);
            $chamado->load("situacaoAtual");
            $this->autorizarConsulta($chamado, $perfis);
            abort_unless($this->perfilPodeCriar($perfis), 403);

            $atualizacao = $this->service->registrar($chamado, $request->validated(), Auth::id());

            return response([
                "cod" => 1,
                "msg" => "Atualização clínica registrada com sucesso.",
                "retorno" => $atualizacao,
                "prioridadeAtual" => (int) $chamado->TG_PRIORIDADE_ID,
                "atualizacoes" => $this->atualizacoes($chamado->CHAMADO_ID),
            ]);
        });
    }

    private function atualizacoes($chamadoId)
    {
        return AtualizacaoClinica::with("usuario")
            ->where("CHAMADO_ID", $chamadoId)
            ->orderBy("ATUALIZACAO_CLINICA_DATA")
            ->orderBy("ATUALIZACAO_CLINICA_ID")
            ->get();
    }

    private function valoresAtuais(Chamado $chamado)
    {
        $ultima = AtualizacaoClinica::where("CHAMADO_ID", $chamado->CHAMADO_ID)
            ->orderByDesc("ATUALIZACAO_CLINICA_DATA")
            ->orderByDesc("ATUALIZACAO_CLINICA_ID")
            ->first();

        if ($ultima) {
            return $ultima->only([
                "TG_PRIORIDADE_ID",
                "TG_TIPO_PRECAUCAO_ID",
                "TG_SUPORTE_O2_ID",
                "TG_SUPORTE_HEMODINAMICO_ID",
                "ATUALIZACAO_CLINICA_TEMPERATURA",
                "ATUALIZACAO_CLINICA_PRESSAO_ARTERIAL",
                "ATUALIZACAO_CLINICA_FREQUENCIA_CARDIACA",
                "ATUALIZACAO_CLINICA_SATURACAO_O2",
                "ATUALIZACAO_CLINICA_ESCALA_GLASGOW",
                "ATUALIZACAO_CLINICA_PROFISSIONAL",
                "ATUALIZACAO_CLINICA_CONSELHO",
                "ATUALIZACAO_CLINICA_NUMERO_CONSELHO",
            ]);
        }

        $profissional = trim((string) $chamado->CHAMADO_PROFISSIONAL_SOLICITANTE);
        if (ctype_digit($profissional)) {
            $cadastro = Profissional::find((int) $profissional);
            $profissional = $cadastro ? $cadastro->PROFISSIONAL_NOME : $profissional;
        }

        return [
            "TG_PRIORIDADE_ID" => $chamado->TG_PRIORIDADE_ID,
            "TG_TIPO_PRECAUCAO_ID" => $chamado->TG_TIPO_PRECAUCAO_ID,
            "TG_SUPORTE_O2_ID" => $chamado->TG_SUPORTE_O2_ID,
            "TG_SUPORTE_HEMODINAMICO_ID" => $chamado->TG_SUPORTE_HEMODINAMICO_ID,
            "ATUALIZACAO_CLINICA_TEMPERATURA" => $chamado->CHAMADO_TEMPERATURA,
            "ATUALIZACAO_CLINICA_PRESSAO_ARTERIAL" => $chamado->CHAMADO_PRESSAO_ARTERIAL,
            "ATUALIZACAO_CLINICA_FREQUENCIA_CARDIACA" => $chamado->CHAMADO_FREQUENCIA_CARDIACA,
            "ATUALIZACAO_CLINICA_SATURACAO_O2" => $chamado->CHAMADO_SATURACAO_O2,
            "ATUALIZACAO_CLINICA_ESCALA_GLASGOW" => $chamado->CHAMADO_ESCALA_GLASGOW,
            "ATUALIZACAO_CLINICA_PROFISSIONAL" => $profissional,
            "ATUALIZACAO_CLINICA_CONSELHO" => $chamado->CHAMADO_CONSELHO_PROFISSIONAL,
            "ATUALIZACAO_CLINICA_NUMERO_CONSELHO" => $chamado->CHAMADO_NUMERO_CONSELHO,
        ];
    }

    private function perfisAtivos()
    {
        return DB::table("USUARIO_PERFIL")
            ->where("USUARIO_ID", Auth::id())
            ->where("USUARIO_PERFIL_ATIVO", 1)
            ->pluck("PERFIL_ID")
            ->map(function ($perfil) {
                return (int) $perfil;
            });
    }

    private function autorizarConsulta(Chamado $chamado, $perfis)
    {
        if ($perfis->intersect([
            PerfilEnum::DESENVOLVEDOR,
            PerfilEnum::ADMINISTRADOR,
            PerfilEnum::REGULADOR_CIA,
        ])->isNotEmpty()) {
            return;
        }

        if ($perfis->contains(PerfilEnum::UNIDADE)) {
            abort_unless(DB::table("USUARIO_UNIDADE")
                ->where("USUARIO_ID", Auth::id())
                ->where("UNIDADE_ID", $chamado->UNIDADE_ID_SOLICITANTE)
                ->exists(), 403);
            return;
        }

        if ($perfis->contains(PerfilEnum::EQUIPE_ASSISTENCIAL)) {
            abort_unless($this->situacaoAtualId($chamado) === SituacaoChamadoEnum::EM_ATENDIMENTO, 403);
            return;
        }

        abort(403);
    }

    private function podeCriar(Chamado $chamado, $perfis)
    {
        return $this->perfilPodeCriar($perfis) && in_array($this->situacaoAtualId($chamado), [
            SituacaoChamadoEnum::ABERTO,
            SituacaoChamadoEnum::EM_ANALISE,
            SituacaoChamadoEnum::EM_ATENDIMENTO,
        ], true);
    }

    private function perfilPodeCriar($perfis)
    {
        return $perfis->intersect([
            PerfilEnum::DESENVOLVEDOR,
            PerfilEnum::ADMINISTRADOR,
            PerfilEnum::REGULADOR_CIA,
            PerfilEnum::UNIDADE,
        ])->isNotEmpty();
    }

    private function situacaoAtualId(Chamado $chamado)
    {
        return $chamado->situacaoAtual ? (int) $chamado->situacaoAtual->TG_SITUACAO_ID : null;
    }
}
