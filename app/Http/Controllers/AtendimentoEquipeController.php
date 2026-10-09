<?php

namespace App\Http\Controllers;

use App\Models\TabelaGenerica;
use App\MyLibs\PerfilEnum;
use App\Services\AtendimentoEquipeService;
use App\Services\EquipeAssistencialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AtendimentoEquipeController extends Controller
{
    private $atendimento;
    private $acessoEquipe;

    public function __construct(AtendimentoEquipeService $atendimento, EquipeAssistencialService $acessoEquipe)
    {
        $this->middleware('auth');
        $this->atendimento = $atendimento;
        $this->acessoEquipe = $acessoEquipe;
    }

    public function view()
    {
        $this->autorizarEquipeAssistencial();

        return view('atendimento_equipe.atendimento_equipe_view', [
            'prioridades' => TabelaGenerica::prioridadePaciente(),
        ]);
    }

    public function fila()
    {
        $this->autorizarEquipeAssistencial();

        return response()->json([
            'chamados' => $this->atendimento->listarFilaDoUsuario(),
            'possuiVinculoProfissional' => $this->acessoEquipe->equipeIdsDoUsuario(true)->isNotEmpty(),
        ]);
    }

    public function avancar(Request $request)
    {
        $this->autorizarEquipeAssistencial();
        $request->validate([
            'CHAMADO_ID' => 'required|integer|exists:CHAMADO,CHAMADO_ID',
            'ETAPA_ESPERADA_ID' => 'required|integer|between:1,5',
        ]);

        $resultado = $this->atendimento->avancar(
            (int) $request->CHAMADO_ID,
            (int) $request->ETAPA_ESPERADA_ID
        );

        return response()->json([
            'cod' => 1,
            'msg' => $resultado['descricao'] . ' registrado com sucesso.',
            'etapa' => $resultado,
        ]);
    }

    public function cancelar(Request $request)
    {
        $this->autorizarEquipeAssistencial();
        $request->merge(['MOTIVO' => trim((string) $request->MOTIVO)]);
        $request->validate([
            'CHAMADO_ID' => 'required|integer|exists:CHAMADO,CHAMADO_ID',
            'MOTIVO' => 'required|string|max:2000',
        ]);

        $this->atendimento->cancelar((int) $request->CHAMADO_ID, $request->MOTIVO);

        return response()->json([
            'cod' => 1,
            'msg' => 'Atendimento cancelado com sucesso.',
        ]);
    }

    private function autorizarEquipeAssistencial()
    {
        $possuiPerfil = DB::table('USUARIO_PERFIL')
            ->where('USUARIO_ID', Auth::id())
            ->where('PERFIL_ID', PerfilEnum::EQUIPE_ASSISTENCIAL)
            ->where('USUARIO_PERFIL_ATIVO', 1)
            ->exists();

        abort_unless($possuiPerfil, 403);
    }
}
