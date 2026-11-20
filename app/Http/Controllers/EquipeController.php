<?php

namespace App\Http\Controllers;

use App\Http\Requests\Equipe\EquipeCreateRequest;
use App\Models\ChamadoEquipe;
use App\Models\Equipe;
use App\Models\EquipeHistorico;
use App\Models\EquipeHistoricoProfissional;
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

    public function inserir(EquipeCreateRequest $request)
    {
        $equipeId = DB::transaction(function () use ($request) {
            // Serializar cadastros da mesma ambulância antes de verificar a duplicidade.
            $veiculo = Veiculo::lockForUpdate()->findOrFail($request->input('VEICULO_ID'));
            $data = Carbon::now()->toDateString();
            $turno = $request->input('EQUIPE_TURNO');
            $this->validarDuplicidade($veiculo->VEICULO_ID, $data, $turno);

            $equipe = Equipe::create([
                'VEICULO_ID' => $veiculo->VEICULO_ID,
                'EQUIPE_DATA' => $data,
                'EQUIPE_TURNO' => $turno,
                'EQUIPE_ATIVO' => 1,
                'USUARIO_ID_CAD' => Auth::id(),
            ]);
            foreach ($request->input('equipeProfissionais') as $dados) {
                Profissional::findOrFail($dados['PROFISSIONAL_ID']);
                $profissional = new EquipeProfissional($dados);
                $profissional->EQUIPE_ID = $equipe->EQUIPE_ID;
                $profissional->EQUIPE_PROFISSIONAL_ATIVO = 1;
                $profissional->USUARIO_ID_CAD = Auth::id();
                $profissional->save();
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
        ]);
        $equipe = DB::transaction(function () use ($dados) {
            $equipe = Equipe::lockForUpdate()->findOrFail($dados['EQUIPE_ID']);
            Veiculo::lockForUpdate()->findOrFail($dados['VEICULO_ID']);
            // Editar uma equipe não deve mudar a data original de seu plantão.
            $this->validarDuplicidade($dados['VEICULO_ID'], $equipe->EQUIPE_DATA->toDateString(),
                $dados['EQUIPE_TURNO'], $equipe->EQUIPE_ID);
            $equipe->fill($dados);
            $equipe->USUARIO_ID_CAD = Auth::id();
            $equipe->save();

            return $equipe;
        }, 3);

        return response($equipe);
    }

    private function validarDuplicidade($veiculoId, $data, $turno, $ignorarId = null)
    {
        $consulta = Equipe::where('VEICULO_ID', $veiculoId)->whereDate('EQUIPE_DATA', $data)
            ->where('EQUIPE_TURNO', $turno);
        if ($ignorarId !== null) {
            $consulta->where('EQUIPE_ID', '!=', $ignorarId);
        }
        if ($consulta->exists()) {
            throw ValidationException::withMessages([
                'EQUIPE_TURNO' => 'Já existe uma equipe deste turno cadastrada para esta ambulância nesta data.',
            ]);
        }
    }

    public function deletar(Request $request)
    {
        
        $EQUIPE_ID = isset($request['EQUIPE_ID']) ? $request['EQUIPE_ID'] : null;

        // Valida antes de apagar qualquer coisa: se a equipe já foi usada em
        // algum chamado, não pode ser excluída (violaria a FK em CHAMADO_EQUIPE
        // e deixaria chamados órfãos).
        
        // Exclusão física, mas arquivando antes no histórico e tudo dentro de
        // uma transação (tudo-ou-nada): se qualquer passo falhar, nada é
        // apagado e nada fica pela metade.
        DB::transaction(function () use ($EQUIPE_ID) {

            //verifico uso em chamado
            $temChamado = ChamadoEquipe::where('EQUIPE_ID', '=', $EQUIPE_ID)->exists();

            //verifica se já houve a inserção da equipe em EQUIPE_HISTORICO
            $equipeHistorico = EquipeHistorico::where('EQUIPE_ID', '=', $EQUIPE_ID)->exists();
            
            //verifica se já houve a inserção da equipe em EQUIPE_HISTORICO_PROFISSIONAL
            $equipeHistoricoProfissional = EquipeHistoricoProfissional::where('EQUIPE_ID', '=', $EQUIPE_ID)->exists();

            if(!$equipeHistorico){
                DB::insert(
                    'INSERT INTO EQUIPE_HISTORICO
                        (EQUIPE_ID, VEICULO_ID, EQUIPE_ATIVO, USUARIO_ID_CAD, EQUIPE_DATA, EQUIPE_TURNO)
                    SELECT EQUIPE_ID, VEICULO_ID, EQUIPE_ATIVO, USUARIO_ID_CAD, EQUIPE_DATA, EQUIPE_TURNO
                    FROM EQUIPE
                    WHERE EQUIPE_ID = ?',
                    [$EQUIPE_ID]
                );
            }

            if(!$equipeHistoricoProfissional){
                // 2) Arquiva os profissionais em EQUIPE_HISTORICO_PROFISSIONAL.
                //    EQUIPE_PROFISSIONAL_ID é IDENTITY no histórico, então é omitido
                //    (o banco gera um novo).
                DB::insert(
                    'INSERT INTO EQUIPE_HISTORICO_PROFISSIONAL
                        (EQUIPE_ID, PROFISSIONAL_ID, EQUIPE_PROFISSIONAL_ATIVO, USUARIO_ID_CAD)
                    SELECT EQUIPE_ID, PROFISSIONAL_ID, EQUIPE_PROFISSIONAL_ATIVO, USUARIO_ID_CAD
                    FROM EQUIPE_PROFISSIONAL
                    WHERE EQUIPE_ID = ?',
                    [$EQUIPE_ID]
                );
            }

            //mudança
            if ($temChamado) {
                //desativa a equipe
                EquipeProfissional::where('EQUIPE_ID', '=', $EQUIPE_ID)
                ->update([
                    'EQUIPE_PROFISSIONAL_ATIVO' => 0
                ]);

                Equipe::where('EQUIPE_ID', '=', $EQUIPE_ID)
                ->update([
                    'EQUIPE_ATIVO' => 0
                ]);

                // return response()->json([
                //     'sucesso' => false,
                //     'mensagem' => 'Equipe já está fazendo atendimento de chamados!'
                // ], 422);
                
                // 3) Exclusão física: profissionais primeiro, depois a equipe.
            }
            else{
                EquipeProfissional::where('EQUIPE_ID', '=', $EQUIPE_ID)
                ->delete();
                
                Equipe::where('EQUIPE_ID', '=', $EQUIPE_ID)
                    ->delete();
            }
        });

        return response()->json([
            'sucesso' => true,
            'mensagem' => 'Equipe excluída com sucesso.'
        ]);

    }

    public function desativarEquipes(Request $request)
    {
        //desativa todas as equipes anteriores a data de hoje
        $hoje   = Carbon::today();
        // DD($hoje);

        Equipe::where('EQUIPE_DATA', '<', $hoje->toDateString())
            ->where('EQUIPE_ATIVO', '=', 1)
            ->update([
                'EQUIPE_ATIVO' => 0,
                'USUARIO_ID_CAD' => Auth::id(),
            ]);

        return response()->json([
            'sucesso' => true,
            'mensagem' => 'Equipe anterior desativada com sucesso.'
        ]);

    }

    public function desativarEquipesTodas(Request $request)
    {
        //desativa todas as equipes anteriores a data de hoje
        Equipe::where('EQUIPE_ATIVO', '=', 1)
            ->update([
                'EQUIPE_ATIVO' => 0,
                'USUARIO_ID_CAD' => Auth::id(),
            ]);

        return response()->json([
            'sucesso' => true,
            'mensagem' => 'Equipe anterior desativada com sucesso.'
        ]);

    }
}