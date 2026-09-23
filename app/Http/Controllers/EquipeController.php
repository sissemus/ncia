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

        return view('equipe.equipe_view', compact('tiposProfissional', 'profissionais', 'tiposVeiculo'));
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

        EquipeProfissional::where('EQUIPE_ID', $request->EQUIPE_ID)
            ->delete();

        Equipe::where('EQUIPE_ID', $request->EQUIPE_ID)
            ->delete();

        // $estaEmUso = ChamadoEquipe::where(
        //     'VEICULO_ID',
        //     $equipe->VEICULO_ID
        // )->exists();

        // if ($estaEmUso) {
        //     // A equipe está vinculada a um chamado:
        //     // apenas desativa
        //     $equipe->EQUIPE_ATIVO = 0;
        //     $equipe->save();

        //     return response()->json([
        //         'sucesso' => true,
        //         'mensagem' => 'A equipe está em uso e foi desativada.',
        //         'dados' => $equipe
        //     ]);
        // }

        // Não está vinculada a nenhum chamado:
        // exclui definitivamente

        return response()->json([
            'sucesso' => true,
            'mensagem' => 'Equipe excluída com sucesso.'
        ]);
    }
}
