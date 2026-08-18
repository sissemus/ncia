<?php

namespace App\Http\Controllers;

use App\Models\EquipeProfissional;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EquipeProfissionalController extends Controller
{
    public function inserir(Request $request)
    {
        $equipeProfissionais = [];
        
        foreach ($request->input() as $dados) {
            $equipeProfissional = new EquipeProfissional($dados);

            $equipeProfissional->EQUIPE_PROFISSIONAL_ATIVO = 1;
            $equipeProfissional->save();

            $equipeProfissionais[] = $equipeProfissional;

        }

        return response($equipeProfissionais, 201);

    }

    public function listar()
    {
        $equipeProfissional = EquipeProfissional::where('EQUIPE_PROFISSIONAL_EXCLUSAO', null)
            ->orderBy('EQUIPE_PROFISSIONAL_ID')
            ->get();
        return response($equipeProfissional);
    }

    public function search(Request $request)
    {
        $equipeProfissionais = EquipeProfissional::pesquisar($request);
        return response($equipeProfissionais);
    }

    public function buscar(Request $request)
    {
        $equipeProfissional = EquipeProfissional::findOrFail($request->id);

        return response($equipeProfissional);
    }

    public function alterar(Request $request)
    {
    
        $equipeProfissional = EquipeProfissional::findOrFail($request->EQUIPE_PROFISSIONAL_ID);

        $equipeProfissional->fill($request->post());

        $equipeProfissional->save();

        return response($equipeProfissional);
    }

    public function deletar(Request $request)
    {

        //NÃO IMPLEMENTADO

        $EQUIPE_PROFISSIONAL_ATIVO = isset($request['EQUIPE_PROFISSIONAL_ATIVO']) ? $request['EQUIPE_PROFISSIONAL_ATIVO'] : null;
        $EQUIPE_ID = isset($request['EQUIPE_ID']) ? $request['EQUIPE_ID'] : null;

        //VERIFICAR SE OS PROFISSIONAIS JÁ FORAM UTILIZADOS

        EquipeProfissional::
            when($EQUIPE_PROFISSIONAL_ATIVO, 
                function($q, $c) {
                    return $q->where('EQUIPE_PROFISSIONAL_ATIVO', '=', $c);
                }
            )
            ->where('EQUIPE_ID', $EQUIPE_ID)
            ->delete();

        // exclui definitivamente
        return response()->json([
            'sucesso' => true,
            'mensagem' => 'Profissionais excluídos com sucesso.'
        ]);
    }
}
