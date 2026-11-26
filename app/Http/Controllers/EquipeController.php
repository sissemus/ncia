<?php

namespace App\Http\Controllers;

use App\Http\Requests\Equipe\EquipeCreateRequest;
use App\Models\Equipe;
use App\Models\EquipeProfissional;
use App\Models\Profissional;
use App\Models\TabelaGenerica;
use App\Models\Veiculo;
use App\Services\EquipeAssistencialService;
use App\Services\TrocaPlantaoService;
use App\Services\PlantaoEquipeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EquipeController extends Controller
{
    public function view()
    {
        $tiposProfissional = TabelaGenerica::tipoProfissional();
        $tiposVeiculo = TabelaGenerica::tipoVeiculo();
        $profissionais = Profissional::listarNPesquisa();
        $podeTrocarPlantao = app(EquipeAssistencialService::class)->usuarioPodeOperarTodasEquipes();

        return view('equipe.equipe_view', compact('tiposProfissional', 'profissionais', 'tiposVeiculo', 'podeTrocarPlantao'));
    }

    public function equipesPlantao(Request $request, EquipeAssistencialService $acesso)
    {
        abort_unless($acesso->usuarioPodeOperarTodasEquipes(), 403);
        $dados = $request->validate([
            'VEICULO_ID' => 'required|integer|min:1',
            'EQUIPE_ORIGEM_ID' => 'required|integer|min:1',
        ]);
        $origem = Equipe::where('VEICULO_ID', $dados['VEICULO_ID'])
            ->where('EQUIPE_ATIVO', 1)->findOrFail($dados['EQUIPE_ORIGEM_ID']);
        $seguinte = PlantaoEquipeService::seguinte($origem->EQUIPE_TURNO, $origem->EQUIPE_DATA);

        $equipes = Equipe::with(['equipeProfissional' => function ($query) {
            $query->where('EQUIPE_PROFISSIONAL_ATIVO', 1)->with('profissional');
        }])->where('VEICULO_ID', $dados['VEICULO_ID'])->where('EQUIPE_ATIVO', 1)
            ->whereHas('equipeProfissional', function ($query) {
                $query->where('EQUIPE_PROFISSIONAL_ATIVO', 1);
            });
        PlantaoEquipeService::filtrarPlantao($equipes, $seguinte);

        return response()->json($equipes->orderBy('EQUIPE_ID')->get());
    }

    public function trocarPlantao(Request $request, EquipeAssistencialService $acesso, TrocaPlantaoService $plantao)
    {
        abort_unless($acesso->usuarioPodeOperarTodasEquipes(), 403);
        $dados = $request->validate([
            'VEICULO_ID' => 'required|integer|min:1',
            'EQUIPE_ID' => 'required|integer|min:1',
            'EQUIPE_ORIGEM_ID' => 'required|integer|min:1',
        ]);
        $quantidade = $plantao->transferir($dados['VEICULO_ID'], $dados['EQUIPE_ORIGEM_ID'], $dados['EQUIPE_ID']);

        return response()->json([
            'transferidos' => $quantidade,
            'mensagem' => $quantidade
                ? $quantidade . ' chamado(s) em fila transferido(s) para a equipe selecionada.'
                : 'Não há chamados em fila para transferir para esta equipe.',
        ]);
    }

    // public function inserir(EquipeCreateRequest $request)
    // {
    //     $equipeId = DB::transaction(function () use ($request) {
    //         // Serializar cadastros da mesma ambulância antes de verificar a duplicidade.
    //         $veiculo = Veiculo::lockForUpdate()->findOrFail($request->input('VEICULO_ID'));
    //         $data = Carbon::now()->toDateString();
    //         $turno = $request->input('EQUIPE_TURNO');
    //         $this->validarDuplicidade($veiculo->VEICULO_ID, $data, $turno);

    //         $equipe = Equipe::create([
    //             'VEICULO_ID' => $veiculo->VEICULO_ID,
    //             'EQUIPE_DATA' => $data,
    //             'EQUIPE_TURNO' => $turno,
    //             'EQUIPE_ATIVO' => 1,
    //             'USUARIO_ID_CAD' => Auth::id(),
    //         ]);
    //         foreach ($request->input('equipeProfissionais') as $dados) {
    //             Profissional::findOrFail($dados['PROFISSIONAL_ID']);
    //             $profissional = new EquipeProfissional($dados);
    //             $profissional->EQUIPE_ID = $equipe->EQUIPE_ID;
    //             $profissional->EQUIPE_PROFISSIONAL_ATIVO = 1;
    //             $profissional->USUARIO_ID_CAD = Auth::id();
    //             $profissional->save();
    //         }

    //         return $equipe->EQUIPE_ID;
    //     }, 3);

    //     return response(Equipe::where('EQUIPE_ID', $equipeId)->get(), 201);
    // }
    public function inserir(EquipeCreateRequest $request)
    {
        $equipeId = DB::transaction(function () use ($request) {
            // Serializar cadastros da mesma ambulância antes de verificar a duplicidade.
            $veiculo = Veiculo::lockForUpdate()->findOrFail($request->input('VEICULO_ID'));

            $turno = $request->input('EQUIPE_TURNO');

            [$dataInicio, $dataFim] = $this->periodoTurno($this->dataBaseTurno($turno), $turno);

            $this->validarDuplicidade($veiculo->VEICULO_ID, $dataInicio->toDateString(), $turno);

            $equipe = Equipe::create([
                'VEICULO_ID' => $veiculo->VEICULO_ID,
                'EQUIPE_DATA' => $dataInicio->format('Y-m-d H:i:s'),
                'EQUIPE_DATA_FIM' => $dataFim->format('Y-m-d H:i:s'),
                'EQUIPE_TURNO' => $turno,
                'EQUIPE_ATIVO' => 1,
                'USUARIO_ID_CAD' => Auth::id(),
            ]);

            foreach ($request->input('equipeProfissionais') as $dados) {
                $profissionalId = (int) $dados['PROFISSIONAL_ID'];

                // Profissional não pode estar em outra equipe ativa no mesmo dia e turno.
                $this->validarProfissionalLivre($profissionalId, $dataInicio->toDateString(), $turno, $equipe->EQUIPE_ID);

                EquipeProfissional::create([
                    'EQUIPE_ID' => $equipe->EQUIPE_ID,
                    'PROFISSIONAL_ID' => $profissionalId,
                    'EQUIPE_PROFISSIONAL_ATIVO' => 1,
                    'USUARIO_ID_CAD' => Auth::id(),
                ]);
            }

            return $equipe->EQUIPE_ID;
        }, 3);

        return response(Equipe::where('EQUIPE_ID', $equipeId)->get(), 201);
    }
    public function listar()
    {
        $equipe = Equipe::where('EQUIPE_EXCLUSAO', null)
            ->orderBy('VEICULO_ID')
            ->get();
        return response($equipe);
    }

    public function search(Request $request)
    {
        $equipes = Equipe::pesquisar($request);
        return response($equipes);
    }

    public function buscar(Request $request)
    {
        $equipe = Equipe::findOrFail($request->id);

        return response($equipe);
    }

    public function alterar(Request $request)
    {
        if (is_string($request->input('EQUIPE_TURNO'))) {
            $request->merge(['EQUIPE_TURNO' => strtoupper(trim($request->input('EQUIPE_TURNO')))]);
        }
        $dados = $request->validate([
            'EQUIPE_ID' => 'required|integer|min:1',
            'VEICULO_ID' => 'required|integer|min:1',
            'EQUIPE_TURNO' => 'required|string|in:SD,SN',
            'EQUIPE_ATIVO' => 'sometimes|required|integer|in:0,1',
            'equipeProfissionais' => 'required|array|min:1',
            'equipeProfissionais.*.PROFISSIONAL_ID' => 'required|integer|distinct',
        ], [], [
            'equipeProfissionais' => '<b>PROFISSIONAIS SELECIONADOS</b>',
        ]);
        $equipe = DB::transaction(function () use ($dados) {
            $equipe = Equipe::lockForUpdate()->findOrFail($dados['EQUIPE_ID']);
            Veiculo::lockForUpdate()->findOrFail($dados['VEICULO_ID']);

            // Editar uma equipe não muda o dia original do plantão; se o turno
            // mudar, recalcula apenas o horário de início/fim.
            $dia = $equipe->EQUIPE_DATA->copy()->startOfDay();
            [$dataInicio, $dataFim] = $this->periodoTurno($dia, $dados['EQUIPE_TURNO']);
            $this->validarDuplicidade($dados['VEICULO_ID'], $dia->toDateString(),
                $dados['EQUIPE_TURNO'], $equipe->EQUIPE_ID);

            $equipe->VEICULO_ID = $dados['VEICULO_ID'];
            $equipe->EQUIPE_TURNO = $dados['EQUIPE_TURNO'];
            if (isset($dados['EQUIPE_ATIVO'])) {
                $equipe->EQUIPE_ATIVO = $dados['EQUIPE_ATIVO'];
            }
            if ($equipe->isDirty('EQUIPE_TURNO')) {
                $equipe->EQUIPE_DATA = $dataInicio;
                $equipe->EQUIPE_DATA_FIM = $dataFim;
            }
            $equipe->USUARIO_ID_CAD = Auth::id();
            $equipe->save();

            $this->sincronizarProfissionais($equipe,
                array_map('intval', array_column($dados['equipeProfissionais'], 'PROFISSIONAL_ID')));

            return $equipe;
        }, 3);

        return response($equipe);
    }

    /**
     * Sincroniza os integrantes da equipe usando exclusão lógica, para manter
     * o rastreio dos profissionais vinculados aos chamados já atendidos.
     */
    private function sincronizarProfissionais(Equipe $equipe, array $profissionalIds)
    {
        $data = $equipe->EQUIPE_DATA->toDateString();
        $vinculos = EquipeProfissional::where('EQUIPE_ID', $equipe->EQUIPE_ID)->get();

        // Remove (logicamente) quem saiu da equipe.
        EquipeProfissional::where('EQUIPE_ID', $equipe->EQUIPE_ID)
            ->where('EQUIPE_PROFISSIONAL_ATIVO', 1)
            ->whereNotIn('PROFISSIONAL_ID', $profissionalIds)
            ->update(['EQUIPE_PROFISSIONAL_ATIVO' => 0, 'USUARIO_ID_CAD' => Auth::id()]);

        foreach ($profissionalIds as $profissionalId) {
            $this->validarProfissionalLivre($profissionalId, $data, $equipe->EQUIPE_TURNO, $equipe->EQUIPE_ID);

            $ativo = $vinculos->first(function ($v) use ($profissionalId) {
                return $v->PROFISSIONAL_ID === $profissionalId && $v->EQUIPE_PROFISSIONAL_ATIVO === 1;
            });
            if ($ativo) {
                continue;
            }

            $inativo = $vinculos->firstWhere('PROFISSIONAL_ID', $profissionalId);
            if ($inativo) {
                $inativo->EQUIPE_PROFISSIONAL_ATIVO = 1;
                $inativo->USUARIO_ID_CAD = Auth::id();
                $inativo->save();
                continue;
            }

            EquipeProfissional::create([
                'EQUIPE_ID' => $equipe->EQUIPE_ID,
                'PROFISSIONAL_ID' => $profissionalId,
                'EQUIPE_PROFISSIONAL_ATIVO' => 1,
                'USUARIO_ID_CAD' => Auth::id(),
            ]);
        }
    }

    /**
     * Dia-base do plantão ao cadastrar: SN cadastrado de madrugada (antes das
     * 07h) é o SN que está em curso, iniciado às 19h de ONTEM (mesma regra de
     * PlantaoEquipeService::vigente()). Nos demais casos, hoje.
     */
    private function dataBaseTurno($turno)
    {
        $vigente = PlantaoEquipeService::vigente();

        if ($turno === 'SN' && $vigente['turno'] === 'SN') {
            return Carbon::parse($vigente['data'])->startOfDay();
        }

        return Carbon::today();
    }

    /**
     * Retorna [início, fim] do plantão do turno no dia informado (SD 07h, SN 19h, 12h de duração).
     */
    private function periodoTurno(Carbon $dia, $turno)
    {
        $horas = ['SD' => 7, 'SN' => 19];

        if (!isset($horas[$turno])) {
            throw ValidationException::withMessages([
                'EQUIPE_TURNO' => "Turno inválido: {$turno}",
            ]);
        }

        $inicio = $dia->copy()->setTime($horas[$turno], 0, 0);

        return [$inicio, $inicio->copy()->addHours(12)];
    }

    /**
     * Impede que o profissional esteja em duas equipes ativas no mesmo dia e turno.
     */
    private function validarProfissionalLivre($profissionalId, $data, $turno, $ignorarEquipeId = null)
    {
        $profissional = Profissional::findOrFail($profissionalId);

        $ocupado = EquipeProfissional::where('PROFISSIONAL_ID', $profissionalId)
            ->where('EQUIPE_PROFISSIONAL_ATIVO', 1)
            ->when($ignorarEquipeId, function ($q) use ($ignorarEquipeId) {
                $q->where('EQUIPE_ID', '<>', $ignorarEquipeId);
            })
            ->whereHas('equipe', function ($q) use ($data, $turno) {
                $q->where('EQUIPE_ATIVO', 1)
                    ->whereDate('EQUIPE_DATA', $data)
                    ->where('EQUIPE_TURNO', $turno);
            })
            ->exists();

        if ($ocupado) {
            throw ValidationException::withMessages([
                'equipeProfissionais' => "O profissional {$profissional->PROFISSIONAL_NOME} já está em outra equipe nesta data e turno.",
            ]);
        }
    }

    private function validarDuplicidade($veiculoId, $data, $turno, $ignorarId = null)
    {
        $consulta = Equipe::where('VEICULO_ID', $veiculoId)->whereDate('EQUIPE_DATA', $data)
            ->where('EQUIPE_TURNO', $turno)
            ->where('EQUIPE_ATIVO', 1);
        if ($ignorarId !== null) {
            $consulta->where('EQUIPE_ID', '!=', $ignorarId);
        }
        if ($consulta->exists()) {
            throw ValidationException::withMessages([
                'EQUIPE_TURNO' => 'Já existe uma equipe deste turno cadastrada para esta ambulância nesta data.',
            ]);
        }
    }

    /**
     * "Desativar equipe" (antiga lixeira). Nunca remove fisicamente: só marca
     * EQUIPE_ATIVO = 0, preservando os vínculos com profissionais e chamados.
     * Bloqueada enquanto houver chamado em fila (trocar plantão) ou em
     * atendimento (concluir/cancelar).
     */
    public function deletar(Request $request)
    {
        $dados = $request->validate([
            'EQUIPE_ID' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($dados) {
            $equipe = Equipe::lockForUpdate()->findOrFail($dados['EQUIPE_ID']);

            if ((int) $equipe->EQUIPE_ATIVO !== 1) {
                return response()->json([
                    'sucesso' => false,
                    'mensagem' => "A equipe Nº {$equipe->EQUIPE_ID} já está desativada.",
                ], 422);
            }

            $pendentes = $equipe->chamadosPendentes()->pluck('CHAMADO_ID');
            if ($pendentes->isNotEmpty()) {
                return response()->json([
                    'sucesso' => false,
                    'mensagem' => "A equipe Nº {$equipe->EQUIPE_ID} possui chamado em fila ou em atendimento (chamado Nº "
                        . $pendentes->implode(', ') . '). Troque o plantão ou conclua/cancele o chamado antes de desativá-la.',
                ], 422);
            }

            Equipe::where('EQUIPE_ID', $equipe->EQUIPE_ID)->update([
                'EQUIPE_ATIVO' => 0,
                'UPDATED_AT' => Carbon::now()->format('Y-m-d H:i:s'),
                'USUARIO_ID_CAD' => Auth::id(),
            ]);

            return response()->json([
                'sucesso' => true,
                'mensagem' => "Equipe Nº {$equipe->EQUIPE_ID} desativada com sucesso.",
            ]);
        }, 3);
    }

    public function desativarEquipesTodas(Request $request)
    {
        $ativas = function () {
            return Equipe::where('EQUIPE_ATIVO', '=', 1);
        };

        return $this->desativarSemPendencia($ativas, 'Equipe(s) desativada(s) com sucesso.');
    }

    /**
     * Desativa as equipes da consulta, mantendo as que têm chamado em fila /
     * em atendimento (essas só saem depois da troca de plantão ou do
     * encerramento do chamado).
     */
    private function desativarSemPendencia(\Closure $consulta, $mensagem)
    {
        $mantidas = $consulta()->comChamadoPendente()->pluck('EQUIPE_ID');

        $desativadas = $consulta()->semChamadoPendente()->update([
            'EQUIPE_ATIVO' => 0,
            'USUARIO_ID_CAD' => Auth::id(),
        ]);

        if ($mantidas->isNotEmpty()) {
            $mensagem = "{$desativadas} equipe(s) desativada(s). Mantida(s) por ter chamado em fila ou em atendimento: Nº "
                . $mantidas->implode(', ') . '.';
        }

        return response()->json([
            'sucesso' => true,
            'desativadas' => $desativadas,
            'mantidas' => $mantidas,
            'mensagem' => $mensagem,
        ]);
    }
}