<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Chamado;
use App\MyLibs\PerfilEnum;
use App\MyLibs\SituacaoChamadoEnum;
use App\Services\ChamadoFluxoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HomeController extends Controller
{
    private $fluxo;

    public function __construct(ChamadoFluxoService $fluxo)
    {
        $this->middleware('auth');
        $this->fluxo = $fluxo;
    }

    public function index()
    {
        $usuarioLogado = Usuario::with(['usuarioPerfis.perfil', 'usuarioUnidades.unidade'])->find(Auth::id());
        $prioridades = \App\Models\TabelaGenerica::prioridadePaciente();

        return view('home', compact('usuarioLogado', 'prioridades'));
    }

    public function chamadosAbertos(Request $request)
    {
        $hoje = Carbon::now('America/Sao_Paulo')->toDateString();
        $perfisAtivos = $this->perfisAtivos();

        $podeVisualizarTodos = $this->podeVisualizarTodos($perfisAtivos);
        $podeVisualizarPorUnidade = $perfisAtivos->contains(PerfilEnum::UNIDADE);

        abort_unless($podeVisualizarTodos || $podeVisualizarPorUnidade, 403);

        $query = $this->consultaPorSituacao(SituacaoChamadoEnum::ABERTO)
            ->whereDate('CHAMADO.CHAMADO_DATA', $hoje);

        if (!$podeVisualizarTodos) {
            $unidadeIds = DB::table('USUARIO_UNIDADE')
                ->where('USUARIO_ID', Auth::id())
                ->pluck('UNIDADE_ID');

            $query->whereIn('CHAMADO.UNIDADE_ID_SOLICITANTE', $unidadeIds);
        }

        return response($query->paginate($request->get('per_page', 15)));
    }

    public function chamadosOperacionais(Request $request)
    {
        $request->validate([
            'situacao' => 'required|integer|in:' . SituacaoChamadoEnum::EM_ANALISE . ',' . SituacaoChamadoEnum::EM_ATENDIMENTO,
        ]);

        $situacao = (int) $request->situacao;
        $perfisAtivos = $this->perfisAtivos();
        $podeVisualizar = $this->podeVisualizarTodos($perfisAtivos)
            || ($situacao === SituacaoChamadoEnum::EM_ATENDIMENTO
                && $perfisAtivos->contains(PerfilEnum::EQUIPE_ASSISTENCIAL));

        abort_unless($podeVisualizar, 403);

        return response(
            $this->consultaPorSituacao($situacao)->paginate($request->get('per_page', 15))
        );
    }

    public function chamadosExpirados(Request $request)
    {
        $perfisAtivos = $this->perfisAtivos();
        abort_unless($this->podeCancelarPorPrazo($perfisAtivos), 403);

        $limite = Carbon::now('America/Sao_Paulo')->subHours(24);
        $query = $this->consultaPorSituacao(SituacaoChamadoEnum::ABERTO)
            ->where('CHAMADO.CHAMADO_DATA', '<=', $limite);

        return response($query->paginate($request->get('per_page', 15)));
    }

    public function cancelarChamadoExpirado(Request $request)
    {
        $perfisAtivos = $this->perfisAtivos();
        abort_unless($this->podeCancelarPorPrazo($perfisAtivos), 403);

        $request->validate([
            'CHAMADO_ID' => 'required|integer',
        ]);

        return DB::transaction(function () use ($request) {
            $chamado = Chamado::lockForUpdate()->findOrFail($request->CHAMADO_ID);
            $this->fluxo->cancelarPorPrazoExcedido($chamado);

            return response([
                'cod' => 1,
                'msg' => 'Chamado cancelado por prazo excedido.',
            ]);
        });
    }

    private function consultaPorSituacao($situacao)
    {
        return Chamado::with(['paciente', 'unidadeSolicitante', 'unidadeDestino', 'procedimentos', 'situacaoAtual'])
            ->join('CHAMADO_SITUACAO as cs', 'CHAMADO.CHAMADO_ID', '=', 'cs.CHAMADO_ID')
            ->where('cs.TG_SITUACAO_ID', $situacao)
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))->from('CHAMADO_SITUACAO as cs2')
                    ->whereColumn('cs2.CHAMADO_ID', 'cs.CHAMADO_ID')
                    ->where(function ($q) {
                        $q->whereColumn('cs2.CHAMADO_SITUACAO_DATA', '>', 'cs.CHAMADO_SITUACAO_DATA')
                            ->orWhere(function ($q) {
                                $q->whereColumn('cs2.CHAMADO_SITUACAO_DATA', '=', 'cs.CHAMADO_SITUACAO_DATA')
                                    ->whereColumn('cs2.CHAMADO_SITUACAO_ID', '>', 'cs.CHAMADO_SITUACAO_ID');
                            });
                    });
            })
            ->select('CHAMADO.*', 'cs.TG_SITUACAO_ID')
            ->orderBy('CHAMADO.CHAMADO_DATA')
            ->orderBy('CHAMADO.TG_PRIORIDADE_ID')
            ->orderByRaw('(SELECT PACIENTE_NOME FROM PACIENTE WHERE PACIENTE.PACIENTE_ID = CHAMADO.PACIENTE_ID)')
            ->orderBy('CHAMADO.CHAMADO_ID');
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

    private function podeVisualizarTodos($perfis)
    {
        return $perfis->intersect([
            PerfilEnum::DESENVOLVEDOR,
            PerfilEnum::ADMINISTRADOR,
            PerfilEnum::REGULADOR_CIA,
        ])->isNotEmpty();
    }

    private function podeCancelarPorPrazo($perfis)
    {
        return $perfis->intersect([
            PerfilEnum::DESENVOLVEDOR,
            PerfilEnum::ADMINISTRADOR,
            PerfilEnum::REGULADOR_CIA,
            PerfilEnum::EQUIPE_ASSISTENCIAL,
        ])->isNotEmpty();
    }
}
