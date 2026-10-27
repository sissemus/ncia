<?php

namespace App\Http\Controllers;

use App\Models\TabelaGenerica;
use App\Models\Chamado;
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
        $this->autorizarAtendimentoEquipe();

        return view('atendimento_equipe.atendimento_equipe_view', [
            'prioridades' => TabelaGenerica::prioridadePaciente(),
            'tiposChamado' => TabelaGenerica::tipoChamado(),
            'sexos' => TabelaGenerica::sexo(),
            'tiposPrecaucao' => TabelaGenerica::tipoPrecaucao(),
            'suportesO2' => TabelaGenerica::suporteO2(),
            'suportesHemodinamicos' => TabelaGenerica::suporteHemodinamico(),
        ]);
    }

    public function chamado($id)
    {
        $this->autorizarAtendimentoEquipe();
        $chamado = Chamado::with([
            'paciente', 'unidadeSolicitante', 'unidadeDestino', 'procedimentos',
            'diagnosticos', 'situacaoAtual', 'situacoes.usuario',
            'atualizacoesClinicas.usuario', 'etapasAtendimento.usuario',
            'viagens.situacoes.usuario', 'viagens.etapasAtendimento.usuario',
            'viagemAtual', 'vinculosEquipe.equipe.veiculo',
            'vinculosEquipe.equipe.equipeProfissional.profissional',
        ])->findOrFail($id);

        abort_unless($this->acessoEquipe->vinculoAtivoDoUsuario($chamado), 403);
        abort_unless($chamado->situacaoAtual && in_array((int) $chamado->situacaoAtual->TG_SITUACAO_ID, [
            \App\MyLibs\SituacaoChamadoEnum::EM_FILA,
            \App\MyLibs\SituacaoChamadoEnum::EM_ATENDIMENTO,
        ], true), 403);

        return response()->json($chamado);
    }

    public function fila()
    {
        $this->autorizarAtendimentoEquipe();

        return response()->json([
            'chamados' => $this->atendimento->listarFilaDoUsuario(),
            'possuiVinculoProfissional' => $this->acessoEquipe->equipeIdsDoUsuario(true)->isNotEmpty(),
        ]);
    }

    public function avancar(Request $request)
    {
        $this->autorizarAtendimentoEquipe();
        $request->validate([
            'CHAMADO_ID' => 'required|integer|exists:CHAMADO,CHAMADO_ID',
            'ETAPA_ESPERADA_ID' => 'required|integer|between:1,9',
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
        $this->autorizarAtendimentoEquipe();
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

    private function autorizarAtendimentoEquipe()
    {
        $possuiPerfil = DB::table('USUARIO_PERFIL')
            ->where('USUARIO_ID', Auth::id())
            ->whereIn('PERFIL_ID', [
                PerfilEnum::EQUIPE_ASSISTENCIAL,
                PerfilEnum::REGULADOR_CIA,
                PerfilEnum::ADMINISTRADOR,
            ])
            ->where('USUARIO_PERFIL_ATIVO', 1)
            ->exists();

        abort_unless($possuiPerfil, 403);
    }
}
