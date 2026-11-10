<?php

namespace App\Http\Controllers;

use App\Http\Requests\Equipe\EquipeCreateRequest;
use App\Http\Requests\Equipe\EquipeUpdateRequest;
use App\Models\ChamadoEquipe;
use App\Models\Equipe;
use App\Models\EquipeProfissional;
use App\Models\Profissional;
use App\Models\TabelaGenerica;
use App\Models\Veiculo;
use App\Services\EquipeAssistencialService;
use App\Services\TrocaPlantaoService;
use App\Services\PlantaoEquipeService;
use Carbon\Carbon;
use Illuminate\Auth\Events\Validated;
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

        $equipes = [];

        $EQUIPE_ID = null;

        $USUARIO_ID_CAD = Auth::id();

        // informações sobre o veículo
        $veiculo = Veiculo::findOrFail($request->input('VEICULO_ID'));
        $tipoVeiculo = $veiculo->TG_TIPO_VEICULO_ID;

        $equipe = new Equipe($request->input());
        $equipe->EQUIPE_ATIVO = 1;
        $equipe->EQUIPE_DATA = now()->format('Y-m-d');
        $equipe->USUARIO_ID_CAD = $USUARIO_ID_CAD;

        DB::beginTransaction();

        // salvando equipe
        $equipe->save();

        $EQUIPE_ID = $equipe->EQUIPE_ID;
        
        $int = 0;
  
        //para o tipo de veículo == 1
        $tiposProfissional_1 = [1, 2, 4];
        $tiposProfissional_1_compare = [];
        
        //para o tipo de veículo == 2
        $tiposProfissional_2 = [1, 2, 3, 4];
        $tiposProfissional_2_compare = [];
        
        foreach ($request->input('equipeProfissionais') as $dados) {

            $PROFISSIONAL_ID = $dados['PROFISSIONAL_ID'];

            $profissional = Profissional::findOrFail($PROFISSIONAL_ID);

            $tipoProfissional  = $profissional->TG_TIPO_PROFISSIONAL_ID;

            // crio um array temporário para armazenar o tipo do veículo
            if($tipoVeiculo == 1){
                array_push($tiposProfissional_1_compare, $tipoProfissional);
            }
            else{
                array_push($tiposProfissional_2_compare, $tipoProfissional);
            }

            $int++;

            // informações da equipeProfissional
            $equipeProfissional = new EquipeProfissional($dados);

            // para cada profissional
            $equipeProfissional->EQUIPE_ID = $EQUIPE_ID;

            $equipeProfissional->EQUIPE_PROFISSIONAL_ATIVO = 1;

            $equipeProfissional->USUARIO_ID_CAD = $USUARIO_ID_CAD;

            $equipeProfissional->save();
    
        }

        //validando se a equipe está completa
        // if($tipoVeiculo == 1){

        //     sort($tiposProfissional_1);
        //     sort($tiposProfissional_1_compare);

        //     // comparando por tipo de equipe
        //     if($tiposProfissional_1 != $tiposProfissional_1_compare){
        //         throw ValidationException::withMessages([
        //             'TG_TIPO_PROFISSIONAL_ID' =>
        //                 'O(s) tipo(s) de profissional(is) informado(s) não é(são) compatível(is) com o tipo de veículo selecionado.'
        //         ]);
        //     }
        // }
        // else{

        //     sort($tiposProfissional_2);
        //     sort($tiposProfissional_2_compare);

        //     if($tiposProfissional_2 != $tiposProfissional_2_compare){
        //         throw ValidationException::withMessages([
        //             'TG_TIPO_PROFISSIONAL_ID' =>
        //                 'O(s) tipo(s) de profissional(is) informado(s) não é(são) compatível(is) com o tipo de veículo selecionado.'
        //         ]);
        //     }
        // }

        DB::commit();
        
        $equipes = Equipe::where('EQUIPE_ID', '=', $EQUIPE_ID)->get();

        return response($equipes, 201);

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
    
    
        $equipe = Equipe::findOrFail($request->EQUIPE_ID);

        $equipe->fill($request->post());

        $equipe->EQUIPE_DATA = now();

        $equipe->USUARIO_ID_CAD = Auth::id();

        $equipe->save();

        return response($equipe);
    }

    public function deletar(Request $request)
    {
        
        $EQUIPE_ID = isset($request['EQUIPE_ID']) ? $request['EQUIPE_ID'] : null;
        // $EQUIPE_ATIVO = isset($request['EQUIPE_ATIVO']) ? $request['EQUIPE_ATIVO'] : null;
        // $VEICULO_ID = isset($request['VEICULO_ID']) ? $request['VEICULO_ID'] : null;

        //VERIFICAR SE A EQUIPE JÁ FOI UTILIZADA

        // $equipe = Equipe::where('EQUIPE_ID', '=', $EQUIPE_ID)->first();

        //verifico se a equipe foi utilizada em algum chamado

        // $chamado_equipe = ChamadoEquipe::where('EQUIPE_ID', '=', $EQUIPE_ID)->first();

        // if ($chamado_equipe != null){

        //     return response()->json([
        //         'sucesso' => false,
        //         'mensagem' => 'Equipe já está fazendo atendimento de chamados!'
        //     ], 422);

        // }

        EquipeProfissional::where('EQUIPE_ID', '=', $EQUIPE_ID)
            ->delete();
        
        Equipe::where('EQUIPE_ID', '=', $EQUIPE_ID)
            ->delete();

        return response()->json([
            'sucesso' => true,
            'mensagem' => 'Equipe excluída com sucesso.'
        ]);

    }
}
