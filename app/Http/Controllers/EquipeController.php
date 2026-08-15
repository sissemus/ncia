<?php

namespace App\Http\Controllers;

use App\Http\Requests\Equipe\EquipeCreateRequest;
use App\Http\Requests\Equipe\EquipeUpdateRequest;
use App\Models\ChamadoEquipe;
use App\Models\Equipe;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EquipeController extends Controller
{
    public function view()
    {
        return view('equipe.equipe_view');
    }

<<<<<<< HEAD
    public function inserir(Request $request)
    {
        $equipes = [];
        
        foreach ($request->input() as $dados) {
            $equipe = new Equipe($dados);

            $equipe->EQUIPE_ATIVO = 1;
            $equipe->EQUIPE_DATA = now();
            $equipe->save();

            $equipes[] = $equipe;

        }

        return response($equipes, 201);

=======
    public function inserir(EquipeCreateRequest $request)
    {
        $equipe = new Equipe($request->input());
        $equipe->EQUIPE_ATIVO = 1;
        $equipe->save();

        return response($equipe, 201);
>>>>>>> b5ca06c (implementar abertura de chamado pela unidade)
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

<<<<<<< HEAD
    public function alterar(Request $request)
    {
    
        $equipe = Equipe::findOrFail($request->EQUIPE_ID);

        $equipe->fill($request->post());

        $equipe->EQUIPE_DATA = now();

        $equipe->save();
=======
    public function alterar(EquipeUpdateRequest $request)
    {
        $equipe = Equipe::findOrFail($request->EQUIPE_ID);
        $equipe->fill($request->post());
        $equipe->save();;
>>>>>>> b5ca06c (implementar abertura de chamado pela unidade)

        return response($equipe);
    }

    public function deletar(Request $request)
    {
<<<<<<< HEAD
        Equipe::where('VEICULO_ID', $request->VEICULO_ID)
            ->where('EQUIPE_DATA', now()->format('Y-m-d'))
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
=======
        $equipe = Equipe::findOrFail($request->id);

        $estaEmUso = ChamadoEquipe::where(
            'EQUIPE_ID',
            $equipe->EQUIPE_ID
        )->exists();

        if ($estaEmUso) {
            // A equipe está vinculada a um chamado:
            // apenas desativa
            $equipe->EQUIPE_ATIVO = 0;
            $equipe->save();

            return response()->json([
                'sucesso' => true,
                'mensagem' => 'A equipe está em uso e foi desativada.',
                'dados' => $equipe
            ]);
        }

        // Não está vinculada a nenhum chamado:
        // exclui definitivamente
        $equipe->delete();
>>>>>>> b5ca06c (implementar abertura de chamado pela unidade)

        return response()->json([
            'sucesso' => true,
            'mensagem' => 'Equipe excluída com sucesso.'
        ]);
    }
}
