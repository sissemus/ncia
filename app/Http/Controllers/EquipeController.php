<?php

namespace App\Http\Controllers;

use App\Http\Requests\Equipe\EquipeCreateRequest;
use App\Http\Requests\Equipe\EquipeUpdateRequest;
use App\Models\ChamadoEquipe;
use App\Models\Equipe;
use App\Models\EquipeProfissional;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Util\Exception;

class EquipeController extends Controller
{
    public function view()
    {
        return view('equipe.equipe_view');
    }

    public function inserir(Request $request)
    {
        $equipes = [];
        $retorno = [];

        $EQUIPE_ID = null;

        DB::beginTransaction();

        try{

            foreach ($request->input() as $dados) {

                $equipeProfissional = new EquipeProfissional($dados);

                if($EQUIPE_ID == null){
                    
                    $equipe = new Equipe($dados);
                    $equipe->EQUIPE_ATIVO = 1;
                    $equipe->EQUIPE_DATA = now()->format('Y-m-d');
                    $equipe->save();

                    $EQUIPE_ID = $equipe->EQUIPE_ID;
                }

                // verificar se o profissional não está em outra equipe para esse mesmo dia

                $equipeProfissional->EQUIPE_ID = $EQUIPE_ID;

                $equipeProfissional->EQUIPE_PROFISSIONAL_ATIVO = 1;
                
                $equipeProfissional->save();
                
            }

            DB::commit();

        }
        catch(Exception $e){

            DB::rollBack();

            $retorno[]=[
                'erro' => 1,
                'mensagem' => 'Erro ao inserir/atualizar Equipe!'

            ];

            return $retorno;

        }       
        
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
