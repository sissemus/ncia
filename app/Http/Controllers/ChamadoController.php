<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chamado\AbrirChamadoRequest;
use App\Models\Chamado;
use App\Models\ChamadoDiagnostico;
use App\Models\ChamadoProcedimento;
use App\Models\ChamadoSituacao;
use App\Models\ChamadoViagem;
use App\Models\Diagnostico;
use App\Models\Paciente;
use App\Models\Procedimento;
use App\Models\Profissional;
use App\Models\TabelaGenerica;
use App\Models\Unidade;
use App\MyLibs\SituacaoChamadoEnum;
use App\Services\TipoChamadoService;
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
            "unidadesSolicitantes",
            "unidadesDestino",
            "procedimentos",
            "diagnosticos"
        ));
    }

    public function consultarRetorno($id)
    {
        $chamado = Chamado::with(['paciente', 'procedimentos', 'diagnosticos', 'viagens'])->findOrFail($id);
        $this->validarRetorno($chamado);
        return response()->json([
            'CHAMADO_ID' => $chamado->CHAMADO_ID,
            'DADOS' => $chamado->viagens->first()->DADOS,
            'paciente' => Paciente::find($chamado->viagens->first()->DADOS['PACIENTE_ID']),
            'procedimentos' => $chamado->procedimentos,
            'diagnosticos' => $chamado->diagnosticos,
        ]);
    }

    public function abrirRetorno(Request $request)
    {
        $request->validate(['CHAMADO_ID' => 'required|integer|exists:CHAMADO,CHAMADO_ID']);
        $chamado = DB::transaction(function () use ($request) {
            $chamado = Chamado::lockForUpdate()->findOrFail($request->CHAMADO_ID);
            $this->validarRetorno($chamado);
            $ida = $chamado->viagens()->first();
            $dados = $ida->DADOS;
            $chamado->fill($dados);
            $origem = $dados['UNIDADE_ID_SOLICITANTE'];
            $setorOrigem = $dados['CHAMADO_SETOR_SOLICITANTE'];
            $leitoOrigem = $dados['CHAMADO_LEITO_SOLICITANTE'];
            $chamado->UNIDADE_ID_SOLICITANTE = $dados['UNIDADE_ID_DESTINO'];
            $chamado->CHAMADO_SETOR_SOLICITANTE = $dados['CHAMADO_SETOR_DESTINO'];
            $chamado->CHAMADO_LEITO_SOLICITANTE = $dados['CHAMADO_LEITO_DESTINO'];
            $chamado->UNIDADE_ID_DESTINO = $origem;
            $chamado->CHAMADO_SETOR_DESTINO = $setorOrigem;
            $chamado->CHAMADO_LEITO_DESTINO = $leitoOrigem;
            $chamado->TG_CHAMADO_ID = TipoChamadoService::id('Retorno');
            $chamado->CHAMADO_DATA = Carbon::now();
            $chamado->save();

            ChamadoViagem::create([
                'CHAMADO_ID' => $chamado->CHAMADO_ID,
                'NUMERO' => (int) $chamado->viagens()->reorder()->max('NUMERO') + 1,
                'RETORNO' => 1,
                'UNIDADE_ID_SOLICITANTE' => $chamado->UNIDADE_ID_SOLICITANTE,
                'TG_CHAMADO_ID' => $chamado->TG_CHAMADO_ID,
                'TG_SITUACAO_ID' => SituacaoChamadoEnum::ABERTO,
                'INICIO' => $chamado->CHAMADO_DATA,
                'DADOS' => $chamado->getAttributes(),
            ]);
            ChamadoSituacao::create([
                'TG_SITUACAO_ID' => SituacaoChamadoEnum::ABERTO,
                'CHAMADO_ID' => $chamado->CHAMADO_ID,
                'CHAMADO_SITUACAO_DATA' => Carbon::now(),
                'CHAMADO_SITUACAO_OBSERVACAO' => 'Retorno solicitado a partir da ida concluída.',
                'USUARIO_ID' => Auth::id(),
            ]);
            return $chamado;
        });
        return response()->json(['cod' => 1, 'msg' => 'Retorno aberto com sucesso', 'retorno' => $chamado]);
    }

    private function validarRetorno(Chamado $chamado)
    {
        $viagens = $chamado->viagens()->get();
        $ida = $viagens->first();
        abort_unless($ida && !$ida->RETORNO
            && (int) $ida->TG_CHAMADO_ID === TipoChamadoService::id('Transporte')
            && (int) $ida->TG_SITUACAO_ID === SituacaoChamadoEnum::CONCLUIDO, 422,
            'A ida deve ser um transporte concluído.');
        $ultima = $viagens->last();
        abort_if($ultima->NUMERO > 1 && (int) $ultima->TG_SITUACAO_ID !== SituacaoChamadoEnum::CANCELADO,
            422, 'Este chamado já possui retorno.');
        $dados = $ida->DADOS;
        $autorizado = DB::table('USUARIO_UNIDADE')->where('USUARIO_ID', Auth::id())
            ->where('UNIDADE_ID', $dados['UNIDADE_ID_SOLICITANTE'])->exists();
        abort_unless($autorizado, 403);
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
                "CHAMADO_TEMPERATURA"          => $request->CHAMADO_TEMPERATURA,
                "CHAMADO_FREQUENCIA_CARDIACA"  => $request->CHAMADO_FREQUENCIA_CARDIACA,
                "CHAMADO_PRESSAO_ARTERIAL"     => $request->CHAMADO_PRESSAO_ARTERIAL,
                "CHAMADO_SATURACAO_O2"         => $request->CHAMADO_SATURACAO_O2,
                "CHAMADO_ESCALA_GLASGOW"       => $request->CHAMADO_ESCALA_GLASGOW,
                "CHAMADO_CONSELHO_PROFISSIONAL" => $request->CHAMADO_CONSELHO_PROFISSIONAL,
                "CHAMADO_NUMERO_CONSELHO"      => $request->CHAMADO_NUMERO_CONSELHO,
                "CHAMADO_PROFISSIONAL_SOLICITANTE" => $request->CHAMADO_PROFISSIONAL_SOLICITANTE,
            ]);

            ChamadoViagem::create([
                'CHAMADO_ID' => $chamado->CHAMADO_ID,
                'NUMERO' => 1,
                'RETORNO' => 0,
                'UNIDADE_ID_SOLICITANTE' => $chamado->UNIDADE_ID_SOLICITANTE,
                'TG_CHAMADO_ID' => $chamado->TG_CHAMADO_ID,
                'TG_SITUACAO_ID' => SituacaoChamadoEnum::ABERTO,
                'INICIO' => $chamado->CHAMADO_DATA,
                'DADOS' => $chamado->getAttributes(),
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
