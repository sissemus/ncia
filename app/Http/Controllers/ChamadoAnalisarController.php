<?php

namespace App\Http\Controllers;

use App\Models\Chamado;
use App\Models\ChamadoEquipe;
use App\Models\ChamadoSituacao;
use App\Models\Equipe;
use App\Models\Profissional;
use App\Models\TabelaGenerica;
use App\Models\Veiculo;
use App\MyLibs\PerfilEnum;
use App\MyLibs\SituacaoChamadoEnum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChamadoAnalisarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function view()
    {
        $this->autorizar();
        return view('chamado_analisar.chamado_analisar_view', [
            'prioridades' => TabelaGenerica::prioridadePaciente(),
            'situacoesChamado' => TabelaGenerica::situacaoChamado(),
            'sexos' => TabelaGenerica::sexo(),
            'tiposChamado' => TabelaGenerica::tipoChamado(),
            'tiposPrecaucao' => TabelaGenerica::tipoPrecaucao(),
            'suportesO2' => TabelaGenerica::suporteO2(),
            'suportesHemodinamicos' => TabelaGenerica::suporteHemodinamico(),
            'temperaturas' => TabelaGenerica::sinaisVitaisTemperatura(),
            'frequenciasCardiacas' => TabelaGenerica::sinaisVitaisFrequenciaCardiaca(),
            'pressoesArteriais' => TabelaGenerica::sinaisVitaisPressaoArterial(),
            'saturacoes' => TabelaGenerica::sinaisVitaisSaturacao(),
            'motivosCancelamento' => TabelaGenerica::motivoCancelamento(),
        ]);
    }

    public function search(Request $request)
    {
        $this->autorizar();
        $request->merge(['TG_SITUACAO_ID' => SituacaoChamadoEnum::EM_ANALISE]);
        return response(Chamado::pesquisarParaAnalise($request));
    }

    public function buscar($id)
    {
        $this->autorizar();
        return response($this->carregarChamado($id));
    }

    public function veiculosDisponiveis()
    {
        $this->autorizar();
        $data = Carbon::today()->format('Y-m-d');

        return response(Veiculo::with(['equipe.equipeProfissional.profissional', 'vinculoAtivo.unidade'])
            ->where('VEICULO_ATIVO', 1)
            ->where('TG_SITUACAO_VEICULO_ID', 1)
            ->whereHas('vinculoAtivo')
            ->whereHas('equipe', function ($query) use ($data) {
                $query->where('EQUIPE_DATA', $data)
                    ->where('EQUIPE_ATIVO', 1)
                    ->whereHas('equipeProfissional', function ($q) {
                        $q->where('EQUIPE_PROFISSIONAL_ATIVO', 1);
                    });
            })->orderBy('VEICULO_IDENTIFICACAO')->get());
    }

    public function recepcionar(Request $request)
    {
        $this->autorizar();
        $request->validate(['CHAMADO_ID' => 'required|integer']);

        return DB::transaction(function () use ($request) {
            $chamado = Chamado::lockForUpdate()->findOrFail($request->CHAMADO_ID);
            $this->validarSituacao($chamado, SituacaoChamadoEnum::ABERTO);
            $this->registrarSituacao($chamado, SituacaoChamadoEnum::EM_ANALISE);
            return response(['cod' => 1, 'retorno' => $this->carregarChamado($chamado->CHAMADO_ID)]);
        });
    }

    public function encaminhar(Request $request)
    {
        $this->autorizar();
        $request->validate(['CHAMADO_ID' => 'required|integer', 'EQUIPE_ID' => 'required|integer']);

        return DB::transaction(function () use ($request) {
            $chamado = Chamado::lockForUpdate()->findOrFail($request->CHAMADO_ID);
            $this->validarSituacao($chamado, SituacaoChamadoEnum::EM_ANALISE);
            $equipe = Equipe::where('EQUIPE_ID', $request->EQUIPE_ID)
                ->where('EQUIPE_DATA', Carbon::today()->format('Y-m-d'))
                ->where('EQUIPE_ATIVO', 1)
                ->whereHas('equipeProfissional', function ($q) { $q->where('EQUIPE_PROFISSIONAL_ATIVO', 1); })
                ->lockForUpdate()->firstOrFail();
            $veiculo = Veiculo::lockForUpdate()->findOrFail($equipe->VEICULO_ID);
            abort_unless($veiculo->VEICULO_ATIVO == 1 && $veiculo->TG_SITUACAO_VEICULO_ID == 1, 422, 'Veículo indisponível.');
            abort_if(ChamadoEquipe::where('EQUIPE_ID', $equipe->EQUIPE_ID)->where('CHAMADO_EQUIPE_ATIVO', 1)->exists(), 422, 'Equipe já está vinculada a outro chamado.');

            ChamadoEquipe::create(['CHAMADO_ID' => $chamado->CHAMADO_ID, 'EQUIPE_ID' => $equipe->EQUIPE_ID, 'CHAMADO_EQUIPE_ATIVO' => 1]);
            $veiculo->TG_SITUACAO_VEICULO_ID = 2;
            $veiculo->save();
            $this->registrarSituacao($chamado, SituacaoChamadoEnum::EM_ATENDIMENTO);
            return response(['cod' => 1, 'retorno' => $this->carregarChamado($chamado->CHAMADO_ID)]);
        });
    }

    public function cancelar(Request $request)
    {
        $this->autorizar();
        $request->validate([
            'CHAMADO_ID' => 'required|integer',
            'MOTIVO_CANCELAMENTO_ID' => 'required|integer',
            'CHAMADO_SITUACAO_OBSERVACAO' => 'required|string',
        ]);

        return DB::transaction(function () use ($request) {
            $chamado = Chamado::lockForUpdate()->findOrFail($request->CHAMADO_ID);
            $this->validarSituacao($chamado, SituacaoChamadoEnum::EM_ANALISE, SituacaoChamadoEnum::EM_ATENDIMENTO);
            $this->registrarSituacao($chamado, SituacaoChamadoEnum::CANCELADO, $request->CHAMADO_SITUACAO_OBSERVACAO, $request->MOTIVO_CANCELAMENTO_ID);
            $this->liberarRecursos($chamado);
            return response(['cod' => 1, 'retorno' => $this->carregarChamado($chamado->CHAMADO_ID)]);
        });
    }

    public function concluir(Request $request)
    {
        $this->autorizar();
        $request->validate(['CHAMADO_ID' => 'required|integer']);

        return DB::transaction(function () use ($request) {
            $chamado = Chamado::lockForUpdate()->findOrFail($request->CHAMADO_ID);
            $this->validarSituacao($chamado, SituacaoChamadoEnum::EM_ATENDIMENTO);
            $this->registrarSituacao($chamado, SituacaoChamadoEnum::CONCLUIDO);
            $this->liberarRecursos($chamado);
            return response(['cod' => 1, 'retorno' => $this->carregarChamado($chamado->CHAMADO_ID)]);
        });
    }

    private function carregarChamado($id)
    {
        $chamado = Chamado::with(['paciente', 'unidadeSolicitante', 'unidadeDestino', 'procedimentos', 'diagnosticos', 'situacoes.usuario', 'situacaoAtual'])->findOrFail($id);
        $profissional = trim((string) $chamado->CHAMADO_PROFISSIONAL_SOLICITANTE);

        if (ctype_digit($profissional)) {
            $profissionalModel = Profissional::find((int) $profissional);
            $profissional = $profissionalModel ? $profissionalModel->PROFISSIONAL_NOME : $profissional;
        }

        $chamado->setAttribute('profissionalSolicitanteNome', $profissional ?: '-');

        return $chamado;
    }

    private function liberarRecursos($chamado)
    {
        $vinculos = ChamadoEquipe::where('CHAMADO_ID', $chamado->CHAMADO_ID)->where('CHAMADO_EQUIPE_ATIVO', 1)->get();
        foreach ($vinculos as $vinculo) {
            $vinculo->update(['CHAMADO_EQUIPE_ATIVO' => 0]);
            $equipe = Equipe::find($vinculo->EQUIPE_ID);
            if ($equipe) Veiculo::where('VEICULO_ID', $equipe->VEICULO_ID)->update(['TG_SITUACAO_VEICULO_ID' => 1]);
        }
    }

    private function registrarSituacao($chamado, $situacao, $observacao = null, $motivo = null)
    {
        ChamadoSituacao::create([
            'CHAMADO_ID' => $chamado->CHAMADO_ID,
            'TG_SITUACAO_ID' => $situacao,
            'CHAMADO_SITUACAO_DATA' => Carbon::now(),
            'CHAMADO_SITUACAO_OBSERVACAO' => $motivo ? 'Motivo: ' . $motivo . ' - ' . $observacao : $observacao,
            'USUARIO_ID' => Auth::id(),
        ]);
    }

    private function validarSituacao($chamado, $esperada, $alternativa = null)
    {
        $atual = $chamado->situacaoAtual()->first();
        abort_unless($atual && ((int) $atual->TG_SITUACAO_ID === $esperada || ($alternativa && (int) $atual->TG_SITUACAO_ID === $alternativa)), 422, 'A situação do chamado foi alterada.');
    }

    private function autorizar()
    {
        $permitido = DB::table('USUARIO_PERFIL')->where('USUARIO_ID', Auth::id())->where('USUARIO_PERFIL_ATIVO', 1)->whereIn('PERFIL_ID', [PerfilEnum::DESENVOLVEDOR, PerfilEnum::ADMINISTRADOR, PerfilEnum::REGULADOR_CIA])->exists();
        abort_unless($permitido, 403);
    }
}
