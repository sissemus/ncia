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

        $unidadesSolicitantes = Unidade::where("UNIDADE_ATIVO", 1)->where("UNIDADE_SOLICITANTE", 1)->whereIn("UNIDADE_ID", DB::table("USUARIO_UNIDADE")->where("USUARIO_ID", Auth::id())->pluck("UNIDADE_ID"))->orderBy("UNIDADE_NOME")->get();
        $unidadesDestino = Unidade::where("UNIDADE_ATIVO", 1)->orderBy("UNIDADE_NOME")->get();
        $profissionais = Profissional::where("PROFISSIONAL_ATIVO", 1)->orderBy("PROFISSIONAL_NOME")->get();
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
            "profissionais",
            "procedimentos",
            "diagnosticos"
        ));
    }

    public function abrir(AbrirChamadoRequest $request)
    {
        $chamado = DB::transaction(function () use ($request) {
            $pacienteId = $request->PACIENTE_ID;

            if ($request->PACIENTE_VULNERABILIDADE_SOCIAL) {
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
                "PACIENTE_ID" => $pacienteId,
                "TG_CHAMADO_ID" => $request->TG_CHAMADO_ID,
                "TG_PRIORIDADE_ID" => $request->TG_PRIORIDADE_ID,
                "CHAMADO_DATA" => now(),
                "CHAMADO_OBSERVACAO" => $request->CHAMADO_OBSERVACAO,
                "UNIDADE_ID_SOLICITANTE" => $request->UNIDADE_ID_SOLICITANTE,
                "UNIDADE_ID_DESTINO" => $request->UNIDADE_ID_DESTINO,
                "CHAMADO_HORARIO_ATENDIMENTO" => $request->CHAMADO_HORARIO_ATENDIMENTO,
                "CHAMADO_SETOR_SOLICITANTE" => $request->CHAMADO_SETOR_SOLICITANTE,
                "CHAMADO_LEITO_SOLICITANTE" => $request->CHAMADO_LEITO_SOLICITANTE,
                "CHAMADO_SETOR_DESTINO" => $request->CHAMADO_SETOR_DESTINO,
                "CHAMADO_LEITO_DESTINO" => $request->CHAMADO_LEITO_DESTINO,
                "CHAMADO_DISPOSITIVOS" => $request->CHAMADO_DISPOSITIVOS,
                "CHAMADO_PESO" => $request->CHAMADO_PESO,
                "TG_TIPO_PRECAUCAO_ID" => $request->TG_TIPO_PRECAUCAO_ID,
                "TG_SUPORTE_O2_ID" => $request->TG_SUPORTE_O2_ID,
                "TG_SUPORTE_HEMODINAMICO_ID" => $request->TG_SUPORTE_HEMODINAMICO_ID,
                "TG_TEMPERATURA_ID" => $request->TG_TEMPERATURA_ID,
                "TG_FREQUENCIA_CARDIACA_ID" => $request->TG_FREQUENCIA_CARDIACA_ID,
                "TG_PRESSAO_ARTERIAL_ID" => $request->TG_PRESSAO_ARTERIAL_ID,
                "TG_SATURACAO_ID" => $request->TG_SATURACAO_ID,
                "PROFISSIONAL_ID_SOLICITANTE" => $request->PROFISSIONAL_ID_SOLICITANTE,
            ]);

            ChamadoProcedimento::create(["CHAMADO_ID" => $chamado->CHAMADO_ID, "PROCEDIMENTO_ID" => $request->PROCEDIMENTO_ID]);

            if ($request->DIAGNOSTICO_ID) {
                ChamadoDiagnostico::create(["CHAMADO_ID" => $chamado->CHAMADO_ID, "DIAGNOSTICO_ID" => $request->DIAGNOSTICO_ID]);
            }

            ChamadoSituacao::create([
                "TG_SITUACAO_ID" => 1,
                "CHAMADO_ID" => $chamado->CHAMADO_ID,
                "CHAMADO_SITUACAO_DATA" => now(),
                "CHAMADO_SITUACAO_OBSERVACAO" => null,
                "USUARIO_ID" => Auth::id(),
            ]);

            return $chamado;
        });

        return response([
            "cod" => 1,
            "msg" => "Chamado aberto com sucesso",
            "retorno" => $chamado
        ], 200);
    }
}
