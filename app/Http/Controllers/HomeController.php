<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Chamado;
use App\MyLibs\PerfilEnum;
use App\MyLibs\SituacaoChamadoEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $usuarioLogado = Usuario::with(['usuarioPerfis.perfil', 'usuarioUnidades.unidade'])->find(Auth::id());
        $prioridades = \App\Models\TabelaGenerica::prioridadePaciente();

        return view('home', compact('usuarioLogado', 'prioridades'));
    }

    public function chamadosAbertos(Request $request)
    {
        $perfisAtivos = DB::table('USUARIO_PERFIL')->where('USUARIO_ID', Auth::id())
            ->where('USUARIO_PERFIL_ATIVO', 1)
            ->pluck('PERFIL_ID');

        $podeVisualizarTodos = $perfisAtivos->intersect([
            PerfilEnum::DESENVOLVEDOR,
            PerfilEnum::ADMINISTRADOR,
            PerfilEnum::REGULADOR_CIA,
        ])->isNotEmpty();
        $podeVisualizarPorUnidade = $perfisAtivos->contains(PerfilEnum::UNIDADE);

        abort_unless($podeVisualizarTodos || $podeVisualizarPorUnidade, 403);

        $query = Chamado::with(['paciente', 'unidadeSolicitante', 'unidadeDestino', 'procedimentos', 'situacaoAtual'])
            ->join('CHAMADO_SITUACAO as cs', 'CHAMADO.CHAMADO_ID', '=', 'cs.CHAMADO_ID')
            ->whereIn('cs.TG_SITUACAO_ID', [
                SituacaoChamadoEnum::ABERTO,
                SituacaoChamadoEnum::EM_ANALISE,
            ])
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

        if (!$podeVisualizarTodos) {
            $unidadeIds = DB::table('USUARIO_UNIDADE')
                ->where('USUARIO_ID', Auth::id())
                ->pluck('UNIDADE_ID');

            $query->whereIn('CHAMADO.UNIDADE_ID_SOLICITANTE', $unidadeIds);
        }

        return response($query->paginate($request->get('per_page', 15)));
    }
}
