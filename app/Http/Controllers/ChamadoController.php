<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chamado\AbrirChamadoRequest;
use App\Models\Chamado;
use App\Models\ChamadoDiagnostico;
use App\Models\ChamadoProcedimento;
use App\Models\ChamadoSituacao;
use App\Models\Diagnostico;
use App\Models\Paciente;
use App\Models\Procedimento;
use App\Models\Profissional;
use App\Models\TabelaGenerica;
use App\Models\Unidade;
use App\MyLibs\SituacaoChamadoEnum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChamadoController extends Controller
{
    public function view()
    {
        $sexos = TabelaGenerica::sexo();
        $prioridades = TabelaGenerica::prioridadePaciente();
        $tiposChamado = TabelaGenerica::tipoChamado();
        $tiposPrecaucao = TabelaGenerica::tipoPrecaucao();
        $suportesO2 = TabelaGenerica::suporteO2();
        $suportesHemodinamicos = TabelaGenerica::suporteHemodinamico();
        $temperaturas = TabelaGenerica::sinaisVitaisTemperatura();
        $frequenciasCardiacas = TabelaGenerica::sinaisVitaisFrequenciaCardiaca();
        $pressoesArteriais = TabelaGenerica::sinaisVitaisPressaoArterial();
        $saturacoes = TabelaGenerica::sinaisVitaisSaturacao();
        $unidadesSolicitantes = Unidade::where("UNIDADE_ATIVO", 1)
            ->where("UNIDADE_SOLICITANTE", 1)
            ->whereIn("UNIDADE_ID", DB::table("USUARIO_UNIDADE")->where("USUARIO_ID", Auth::id())->pluck("UNIDADE_ID"))
            ->orderBy("UNIDADE_NOME")
            ->get();
        $unidadesDestino = Unidade::where("UNIDADE_ATIVO", 1)->orderBy("UNIDADE_NOME")->get();
        $procedimentos = Procedimento::where("PROCEDIMENTO_ATIVO", 1)->orderBy("PROCEDIMENTO_DESCRICAO")->get();
        $diagnosticos = Diagnostico::where("DIAGNOSTICO_ATIVO", 1)->orderBy("DIAGNOSTICO_DESCRICAO")->get();

        return view("chamado.chamado_view", compact(
            "sexos",
            "prioridades",
            "tiposChamado",
            "tiposPrecaucao",
            "suportesO2",
            "suportesHemodinamicos",
            "temperaturas",
            "frequenciasCardiacas",
            "pressoesArteriais",
            "saturacoes",
            "unidadesSolicitantes",
            "unidadesDestino",
            "procedimentos",
            "diagnosticos"
        ));
    }

    public function verificarDuplicidade(Request $request)
    {
        $request->validate([
            "PACIENTE_ID" => ["required", "integer", "exists:PACIENTE,PACIENTE_ID"],
            "UNIDADE_ID_SOLICITANTE" => ["required", "integer"],
        ]);

        $unidadePermitida = Unidade::where("UNIDADE_ID", $request->UNIDADE_ID_SOLICITANTE)
            ->where("UNIDADE_SOLICITANTE", 1)
            ->where("UNIDADE_ATIVO", 1)
            ->whereIn("UNIDADE_ID", DB::table("USUARIO_UNIDADE")
                ->where("USUARIO_ID", Auth::id())
                ->pluck("UNIDADE_ID"))
            ->exists();

        abort_unless($unidadePermitida, 403);

        return response([
            "cod" => 1,
            "msg" => "Consulta de duplicidade realizada",
            "retorno" => $this->duplicidadeDoChamado($request->PACIENTE_ID, $request->UNIDADE_ID_SOLICITANTE),
        ]);
    }

    public function abrir(AbrirChamadoRequest $request)
    {
        if (!$request->boolean("PACIENTE_VULNERABILIDADE_SOCIAL") && $request->filled("PACIENTE_ID")) {
            $duplicidade = $this->duplicidadeDoChamado(
                $request->PACIENTE_ID,
                $request->UNIDADE_ID_SOLICITANTE
            );

            if ($duplicidade && !$request->boolean("CONFIRMAR_DUPLICIDADE")) {
                return response([
                    "cod" => 2,
                    "msg" => "Já existe um chamado deste paciente hoje nesta unidade solicitante.",
                    "retorno" => $duplicidade,
                ]);
            }
        }

        $chamado = DB::transaction(function () use ($request) {
            $pacienteId = $request->PACIENTE_ID;

            if ($request->boolean("PACIENTE_VULNERABILIDADE_SOCIAL")) {
                $paciente = new Paciente();
                $paciente->PACIENTE_NOME = $request->PACIENTE_NOME;
                $paciente->PACIENTE_CPF = null;
                $paciente->PACIENTE_DT_NASCIMENTO = $request->PACIENTE_DT_NASCIMENTO;
                $paciente->TG_SEXO_ID = $request->TG_SEXO_ID;
                $paciente->PACIENTE_VULNERABILIDADE_SOCIAL = 1;
                $paciente->PACIENTE_TEMPORARIO = 1;
                $paciente->USUARIO_ID = Auth::id();
                $paciente->PACIENTE_DT_CAD = now();
                $paciente->PACIENTE_DT_IDENTIFICACAO = null;
                $paciente->save();

                $pacienteId = $paciente->PACIENTE_ID;
            }

            $chamado = Chamado::create([
                "PACIENTE_ID"                  => $pacienteId,
                "TG_CHAMADO_ID"                => $request->TG_CHAMADO_ID,
                "TG_PRIORIDADE_ID"             => $request->TG_PRIORIDADE_ID,
                "CHAMADO_AMBULANCIA_EXTRA"     => $request->CHAMADO_AMBULANCIA_EXTRA,
                "CHAMADO_DATA"                 => Carbon::now(),
                "CHAMADO_OBSERVACAO"           => $request->CHAMADO_OBSERVACAO,
                "UNIDADE_ID_SOLICITANTE"       => $request->UNIDADE_ID_SOLICITANTE,
                "UNIDADE_ID_DESTINO"           => $request->UNIDADE_ID_DESTINO,
                "CHAMADO_HORARIO_ATENDIMENTO"  => $request->CHAMADO_HORARIO_ATENDIMENTO,
                "CHAMADO_SETOR_SOLICITANTE"    => $request->CHAMADO_SETOR_SOLICITANTE,
                "CHAMADO_LEITO_SOLICITANTE"    => $request->CHAMADO_LEITO_SOLICITANTE,
                "CHAMADO_SETOR_DESTINO"        => $request->CHAMADO_SETOR_DESTINO,
                "CHAMADO_LEITO_DESTINO"        => $request->CHAMADO_LEITO_DESTINO,
                "CHAMADO_DISPOSITIVOS"         => $request->CHAMADO_DISPOSITIVOS,
                "CHAMADO_PESO"                 => $request->CHAMADO_PESO,
                "TG_TIPO_PRECAUCAO_ID"         => $request->TG_TIPO_PRECAUCAO_ID,
                "TG_SUPORTE_O2_ID"             => $request->TG_SUPORTE_O2_ID,
                "TG_SUPORTE_HEMODINAMICO_ID"   => $request->TG_SUPORTE_HEMODINAMICO_ID,
                "TG_TEMPERATURA_ID"            => $request->TG_TEMPERATURA_ID,
                "TG_FREQUENCIA_CARDIACA_ID"    => $request->TG_FREQUENCIA_CARDIACA_ID,
                "TG_PRESSAO_ARTERIAL_ID"       => $request->TG_PRESSAO_ARTERIAL_ID,
                "TG_SATURACAO_ID"              => $request->TG_SATURACAO_ID,
                "CHAMADO_PROFISSIONAL_SOLICITANTE" => $request->CHAMADO_PROFISSIONAL_SOLICITANTE,
            ]);

            ChamadoProcedimento::create([
                "CHAMADO_ID"      => $chamado->CHAMADO_ID,
                "PROCEDIMENTO_ID" => $request->PROCEDIMENTO_ID,
            ]);

            if ($request->DIAGNOSTICO_ID) {
                ChamadoDiagnostico::create([
                    "CHAMADO_ID"     => $chamado->CHAMADO_ID,
                    "DIAGNOSTICO_ID" => $request->DIAGNOSTICO_ID,
                ]);
            }

            ChamadoSituacao::create([
                "TG_SITUACAO_ID"              => SituacaoChamadoEnum::ABERTO,
                "CHAMADO_ID"                  => $chamado->CHAMADO_ID,
                "CHAMADO_SITUACAO_DATA"       => Carbon::now(),
                "CHAMADO_SITUACAO_OBSERVACAO" => null,
                "USUARIO_ID"                  => Auth::id(),
            ]);

            return $chamado;
        });

        return response([
            "cod" => 1,
            "msg" => "Chamado aberto com sucesso",
            "retorno" => $chamado
        ], 200);
    }

    private function duplicidadeDoChamado($pacienteId, $unidadeId)
    {
        $inicioHoje = Carbon::today();
        $inicioAmanha = $inicioHoje->copy()->addDay();

        $chamado = Chamado::where("PACIENTE_ID", $pacienteId)
            ->where("UNIDADE_ID_SOLICITANTE", $unidadeId)
            ->where("CHAMADO_DATA", ">=", $inicioHoje)
            ->where("CHAMADO_DATA", "<", $inicioAmanha)
            ->orderByDesc("CHAMADO_DATA")
            ->orderByDesc("CHAMADO_ID")
            ->first();

        if (!$chamado) return null;

        $ultimaSituacao = ChamadoSituacao::where("CHAMADO_ID", $chamado->CHAMADO_ID)
            ->orderByDesc("CHAMADO_SITUACAO_DATA")
            ->orderByDesc("CHAMADO_SITUACAO_ID")
            ->first();

        if (!$ultimaSituacao || !in_array((int) $ultimaSituacao->TG_SITUACAO_ID, [
            SituacaoChamadoEnum::ABERTO,
            SituacaoChamadoEnum::EM_ANALISE,
            SituacaoChamadoEnum::EM_ATENDIMENTO,
        ], true)) return null;

        $situacao = TabelaGenerica::situacaoChamado()
            ->firstWhere("COLUNA_ID", (int) $ultimaSituacao->TG_SITUACAO_ID);

        return [
            "CHAMADO_ID" => $chamado->CHAMADO_ID,
            "TG_SITUACAO_ID" => $ultimaSituacao->TG_SITUACAO_ID,
            "SITUACAO_DESCRICAO" => $situacao ? $situacao->DESCRICAO : "Em andamento",
            "CHAMADO_DATA" => $chamado->CHAMADO_DATA,
            "UNIDADE_ID_SOLICITANTE" => $chamado->UNIDADE_ID_SOLICITANTE,
        ];

    }
}
