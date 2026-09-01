<?php

namespace App\Http\Controllers;

use App\Models\Chamado;
use App\Models\Profissional;
use App\Models\TabelaGenerica;
use App\MyLibs\PerfilEnum;
use App\MyLibs\SituacaoChamadoEnum;
use App\Services\ChamadoFluxoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChamadoAcompanhamentoController extends Controller
{
    private $fluxo;

    public function __construct(ChamadoFluxoService $fluxo)
    {
        $this->middleware('auth');
        $this->fluxo = $fluxo;
    }

    public function view()
    {
        $perfis = $this->perfisAtivos();
        $this->autorizarVisualizacao($perfis);

        $prioridades = TabelaGenerica::prioridadePaciente();
        $situacoesChamado = TabelaGenerica::situacaoChamado();
        $sexos = TabelaGenerica::sexo();
        $tiposChamado = TabelaGenerica::tipoChamado();
        $tiposPrecaucao = TabelaGenerica::tipoPrecaucao();
        $suportesO2 = TabelaGenerica::suporteO2();
        $suportesHemodinamicos = TabelaGenerica::suporteHemodinamico();
        $temperaturas = TabelaGenerica::sinaisVitaisTemperatura();
        $frequenciasCardiacas = TabelaGenerica::sinaisVitaisFrequenciaCardiaca();
        $pressoesArteriais = TabelaGenerica::sinaisVitaisPressaoArterial();
        $saturacoes = TabelaGenerica::sinaisVitaisSaturacao();
        $motivosCancelamento = TabelaGenerica::motivoCancelamento();
        $podeEncerrar = $this->podeEncerrar($perfis);
        $somenteEmAtendimento = $perfis->contains(PerfilEnum::EQUIPE_ASSISTENCIAL)
            && !$this->podeVisualizarTodos($perfis);

        return view("chamado_acompanhamento.chamado_acompanhamento_view", compact(
            'prioridades',
            'situacoesChamado',
            'sexos',
            'tiposChamado',
            'tiposPrecaucao',
            'suportesO2',
            'suportesHemodinamicos',
            'temperaturas',
            'frequenciasCardiacas',
            'pressoesArteriais',
            'saturacoes',
            'motivosCancelamento',
            'podeEncerrar',
            'somenteEmAtendimento'
        ));
    }

    public function search(Request $request)
    {
        $perfis = $this->perfisAtivos();
        $this->autorizarVisualizacao($perfis);

        $userUnidades = null;

        if ($this->podeVisualizarTodos($perfis)) {
            $userUnidades = null;
        } elseif ($perfis->contains(PerfilEnum::EQUIPE_ASSISTENCIAL)) {
            $request->merge(['TG_SITUACAO_ID' => SituacaoChamadoEnum::EM_ATENDIMENTO]);
        } elseif ($perfis->contains(PerfilEnum::UNIDADE)) {
            $userUnidades = $this->unidadesDoUsuario();

            if (empty($userUnidades)) {
                return response()->json([
                    'current_page' => 1,
                    'data' => [],
                    'total' => 0,
                    'last_page' => 1
                ]);
            }
        }

        $chamados = Chamado::pesquisarAcompanhamento($request, $userUnidades);

        return response($chamados);
    }

    public function buscar($id)
    {
        $perfis = $this->perfisAtivos();
        $this->autorizarVisualizacao($perfis);

        $chamado = $this->carregarChamado($id);
        $this->autorizarChamado($chamado, $perfis);

        return response($chamado);
    }

    public function cancelar(Request $request)
    {
        $perfis = $this->perfisAtivos();
        abort_unless($this->podeEncerrar($perfis), 403);

        $request->merge([
            'CHAMADO_SITUACAO_OBSERVACAO' => trim((string) $request->CHAMADO_SITUACAO_OBSERVACAO),
        ]);

        $request->validate([
            'CHAMADO_ID' => 'required|integer',
            'MOTIVO_CANCELAMENTO_ID' => 'required|integer',
            'CHAMADO_SITUACAO_OBSERVACAO' => 'required|string',
        ]);

        return DB::transaction(function () use ($request, $perfis) {
            $chamado = Chamado::lockForUpdate()->findOrFail($request->CHAMADO_ID);
            $this->autorizarChamado($chamado, $perfis);

            $this->fluxo->cancelarAtendimento(
                $chamado,
                $request->MOTIVO_CANCELAMENTO_ID,
                $request->CHAMADO_SITUACAO_OBSERVACAO
            );

            return response([
                'cod' => 1,
                'retorno' => $this->carregarChamado($chamado->CHAMADO_ID)
            ]);
        });
    }

    public function concluir(Request $request)
    {
        $perfis = $this->perfisAtivos();
        abort_unless($this->podeEncerrar($perfis), 403);

        $request->validate([
            'CHAMADO_ID' => 'required|integer'
        ]);

        return DB::transaction(function () use ($request, $perfis) {
            $chamado = Chamado::lockForUpdate()->findOrFail($request->CHAMADO_ID);
            $this->autorizarChamado($chamado, $perfis);
            $this->fluxo->concluirAtendimento($chamado);

            return response([
                'cod' => 1,
                'retorno' => $this->carregarChamado($chamado->CHAMADO_ID)
            ]);
        });
    }

    private function carregarChamado($id)
    {
        $chamado = Chamado::with([
            'paciente',
            'unidadeSolicitante',
            'unidadeDestino',
            'procedimentos',
            'diagnosticos',
            'situacoes',
            'situacoes.usuario',
            'situacaoAtual',
            'vinculosEquipe.equipe.veiculo',
            'vinculosEquipe.equipe.equipeProfissional.profissional'
        ])->findOrFail($id);

        $profissional = trim((string) $chamado->CHAMADO_PROFISSIONAL_SOLICITANTE);

        if (ctype_digit($profissional)) {
            $profissionalModel = Profissional::find((int) $profissional);
            $profissional = $profissionalModel ? $profissionalModel->PROFISSIONAL_NOME : $profissional;
        }

        $chamado->setAttribute('profissionalSolicitanteNome', $profissional ?: '-');

        return $chamado;
    }

    private function autorizarChamado(Chamado $chamado, $perfis)
    {
        if ($this->podeVisualizarTodos($perfis)) {
            return;
        }

        if ($perfis->contains(PerfilEnum::EQUIPE_ASSISTENCIAL)) {
            abort_unless(
                $chamado->situacaoAtual
                    && (int) $chamado->situacaoAtual->TG_SITUACAO_ID === SituacaoChamadoEnum::EM_ATENDIMENTO,
                403
            );

            return;
        }

        if ($perfis->contains(PerfilEnum::UNIDADE)) {
            abort_unless(
                in_array((int) $chamado->UNIDADE_ID_SOLICITANTE, $this->unidadesDoUsuario(), true),
                403
            );

            return;
        }

        abort(403);
    }

    private function perfisAtivos()
    {
        return DB::table('USUARIO_PERFIL')
            ->where('USUARIO_ID', Auth::id())
            ->where('USUARIO_PERFIL_ATIVO', 1)
            ->pluck('PERFIL_ID')
            ->map(function ($perfil) {
                return (int) $perfil;
            });
    }

    private function unidadesDoUsuario()
    {
        return DB::table("USUARIO_UNIDADE")
            ->where("USUARIO_ID", Auth::id())
            ->pluck("UNIDADE_ID")
            ->map(function ($unidade) {
                return (int) $unidade;
            })
            ->all();
    }

    private function podeVisualizarTodos($perfis)
    {
        return $perfis->intersect([
            PerfilEnum::DESENVOLVEDOR,
            PerfilEnum::ADMINISTRADOR,
            PerfilEnum::REGULADOR_CIA,
        ])->isNotEmpty();
    }

    private function podeEncerrar($perfis)
    {
        return $perfis->intersect([
            PerfilEnum::ADMINISTRADOR,
            PerfilEnum::REGULADOR_CIA,
            PerfilEnum::EQUIPE_ASSISTENCIAL,
        ])->isNotEmpty();
    }

    private function autorizarVisualizacao($perfis)
    {
        abort_unless($perfis->intersect([
            PerfilEnum::DESENVOLVEDOR,
            PerfilEnum::ADMINISTRADOR,
            PerfilEnum::REGULADOR_CIA,
            PerfilEnum::UNIDADE,
            PerfilEnum::EQUIPE_ASSISTENCIAL,
        ])->isNotEmpty(), 403);
    }
}
