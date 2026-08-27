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
        $usuarioLogado = Usuario::with('usuarioPerfis')->find(Auth::id());
        $prioridades = \App\Models\TabelaGenerica::prioridadePaciente();

        return view('home', compact('usuarioLogado', 'prioridades'));
    }

    public function chamadosAbertos(Request $request)
    {
        $permitido = DB::table('USUARIO_PERFIL')->where('USUARIO_ID', Auth::id())
            ->where('USUARIO_PERFIL_ATIVO', 1)
            ->whereIn('PERFIL_ID', [PerfilEnum::DESENVOLVEDOR, PerfilEnum::ADMINISTRADOR, PerfilEnum::REGULADOR_CIA])->exists();
        abort_unless($permitido, 403);

        $query = Chamado::with(['paciente', 'unidadeSolicitante', 'unidadeDestino', 'procedimentos', 'situacaoAtual'])
            ->join('CHAMADO_SITUACAO as cs', 'CHAMADO.CHAMADO_ID', '=', 'cs.CHAMADO_ID')
            ->where('cs.TG_SITUACAO_ID', SituacaoChamadoEnum::ABERTO)
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
            ->orderByDesc('CHAMADO.CHAMADO_DATA')
            ->orderByRaw('(SELECT MIN(p.PROCEDIMENTO_DESCRICAO) FROM CHAMADO_PROCEDIMENTO cp JOIN PROCEDIMENTO p ON p.PROCEDIMENTO_ID = cp.PROCEDIMENTO_ID WHERE cp.CHAMADO_ID = CHAMADO.CHAMADO_ID)')
            ->orderBy('CHAMADO.TG_PRIORIDADE_ID');

        return response($query->paginate($request->get('per_page', 15)));
    }
}
