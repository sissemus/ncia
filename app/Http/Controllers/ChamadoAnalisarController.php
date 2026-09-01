<?php

namespace App\Http\Controllers;

use App\Models\Chamado;
use App\Models\Profissional;
use App\Models\TabelaGenerica;
use App\Models\Veiculo;
use App\MyLibs\PerfilEnum;
use App\MyLibs\SituacaoChamadoEnum;
use App\Services\ChamadoFluxoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChamadoAnalisarController extends Controller
{
    private $fluxo;

    public function __construct(ChamadoFluxoService $fluxo)
    {
        $this->middleware('auth');
        $this->fluxo = $fluxo;
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
        $data = Carbon::now('America/Sao_Paulo')->toDateString();

        $veiculos = Veiculo::with([
            'equipe' => function ($query) use ($data) {
                $query->where('EQUIPE_DATA', $data)
                    ->where('EQUIPE_ATIVO', 1)
                    ->orderByDesc('EQUIPE_ID');
            },
            'equipe.equipeProfissional' => function ($query) {
                $query->where('EQUIPE_PROFISSIONAL_ATIVO', 1);
            },
            'equipe.equipeProfissional.profissional',
            'vinculoAtivo.unidade',
        ])
            ->where('VEICULO_ATIVO', 1)
            ->where('TG_SITUACAO_VEICULO_ID', 1)
            ->whereHas('vinculoAtivo')
            ->whereHas('equipe', function ($query) use ($data) {
                $query->where('EQUIPE_DATA', $data)
                    ->where('EQUIPE_ATIVO', 1)
                    ->whereHas('equipeProfissional', function ($profissionais) {
                        $profissionais->where('EQUIPE_PROFISSIONAL_ATIVO', 1);
                    });
            })
            ->orderBy('VEICULO_IDENTIFICACAO')
            ->get();

        // Retorna somente os campos usados pelo modal. Além de reduzir a resposta,
        // evita que colunas legadas com codificação inválida interrompam o JSON.
        return response()->json($veiculos->map(function ($veiculo) {
            $equipe = $veiculo->equipe;
            $vinculo = $veiculo->vinculoAtivo;

            return [
                'VEICULO_ID' => $veiculo->VEICULO_ID,
                'VEICULO_IDENTIFICACAO' => $this->textoJson($veiculo->VEICULO_IDENTIFICACAO),
                'VEICULO_PLACA' => $this->textoJson($veiculo->VEICULO_PLACA),
                'equipe' => $equipe ? [
                    'EQUIPE_ID' => $equipe->EQUIPE_ID,
                    'VEICULO_ID' => $equipe->VEICULO_ID,
                    'EQUIPE_DATA' => $equipe->EQUIPE_DATA,
                    'EQUIPE_TURNO' => $this->textoJson($equipe->EQUIPE_TURNO),
                    'equipeProfissional' => $equipe->equipeProfissional->map(function ($item) {
                        return [
                            'EQUIPE_PROFISSIONAL_ID' => $item->EQUIPE_PROFISSIONAL_ID,
                            'PROFISSIONAL_ID' => $item->PROFISSIONAL_ID,
                            'EQUIPE_PROFISSIONAL_ATIVO' => $item->EQUIPE_PROFISSIONAL_ATIVO,
                            'profissional' => $item->profissional ? [
                                'PROFISSIONAL_ID' => $item->profissional->PROFISSIONAL_ID,
                                'PROFISSIONAL_NOME' => $this->textoJson($item->profissional->PROFISSIONAL_NOME),
                            ] : null,
                        ];
                    })->values(),
                ] : null,
                'vinculoAtivo' => $vinculo ? [
                    'VEICULO_UNIDADE_ID' => $vinculo->VEICULO_UNIDADE_ID,
                    'UNIDADE_ID' => $vinculo->UNIDADE_ID,
                    'unidade' => $vinculo->unidade ? [
                        'UNIDADE_ID' => $vinculo->unidade->UNIDADE_ID,
                        'UNIDADE_NOME' => $this->textoJson($vinculo->unidade->UNIDADE_NOME),
                    ] : null,
                ] : null,
            ];
        })->values());
    }

    public function recepcionar(Request $request)
    {
        $this->autorizar();
        $request->validate(['CHAMADO_ID' => 'required|integer']);

        return DB::transaction(function () use ($request) {
            $chamado = Chamado::lockForUpdate()->findOrFail($request->CHAMADO_ID);
            $this->fluxo->recepcionar($chamado);

            return response(['cod' => 1, 'retorno' => $this->carregarChamado($chamado->CHAMADO_ID)]);
        });
    }

    public function encaminhar(Request $request)
    {
        $this->autorizar();
        $request->validate([
            'CHAMADO_ID' => 'required|integer',
            'EQUIPE_ID' => 'required|integer',
        ]);

        return DB::transaction(function () use ($request) {
            $chamado = Chamado::lockForUpdate()->findOrFail($request->CHAMADO_ID);
            $this->fluxo->encaminhar($chamado, $request->EQUIPE_ID);

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
            $this->fluxo->cancelarAnalise(
                $chamado,
                $request->MOTIVO_CANCELAMENTO_ID,
                $request->CHAMADO_SITUACAO_OBSERVACAO
            );

            return response(['cod' => 1, 'retorno' => $this->carregarChamado($chamado->CHAMADO_ID)]);
        });
    }

    // Mantido para compatibilidade. A interface encerra atendimentos pelo acompanhamento.
    public function concluir(Request $request)
    {
        $this->autorizar();
        $request->validate(['CHAMADO_ID' => 'required|integer']);

        return DB::transaction(function () use ($request) {
            $chamado = Chamado::lockForUpdate()->findOrFail($request->CHAMADO_ID);
            $this->fluxo->concluirAtendimento($chamado);

            return response(['cod' => 1, 'retorno' => $this->carregarChamado($chamado->CHAMADO_ID)]);
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
            'situacoes.usuario',
            'situacaoAtual',
            'vinculosEquipe.equipe.veiculo',
            'vinculosEquipe.equipe.equipeProfissional.profissional',
        ])->findOrFail($id);

        $profissional = trim((string) $chamado->CHAMADO_PROFISSIONAL_SOLICITANTE);
        if (ctype_digit($profissional)) {
            $profissionalModel = Profissional::find((int) $profissional);
            $profissional = $profissionalModel ? $profissionalModel->PROFISSIONAL_NOME : $profissional;
        }

        $chamado->setAttribute('profissionalSolicitanteNome', $profissional ?: '-');

        return $chamado;
    }

    private function autorizar()
    {
        $permitido = DB::table('USUARIO_PERFIL')
            ->where('USUARIO_ID', Auth::id())
            ->where('USUARIO_PERFIL_ATIVO', 1)
            ->whereIn('PERFIL_ID', [
                PerfilEnum::DESENVOLVEDOR,
                PerfilEnum::ADMINISTRADOR,
                PerfilEnum::REGULADOR_CIA,
            ])
            ->exists();

        abort_unless($permitido, 403);
    }

    private function textoJson($valor)
    {
        if ($valor === null) {
            return null;
        }

        $texto = (string) $valor;
        if (mb_check_encoding($texto, 'UTF-8')) {
            return $texto;
        }

        return mb_convert_encoding($texto, 'UTF-8', 'Windows-1252');
    }
}
