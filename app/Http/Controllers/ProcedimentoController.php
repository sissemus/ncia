<?php

namespace App\Http\Controllers;

use App\Http\Requests\Procedimento\ProcedimentoCreateRequest;
use App\Http\Requests\Procedimento\ProcedimentoUpdateRequest;
use App\Models\Procedimento;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProcedimentoController extends Controller
{
    public function view()
    {
        return view('procedimento.procedimento_view');
    }

    public function inserir(ProcedimentoCreateRequest $request)
    {
        $procedimento = new Procedimento($request->input());
        $procedimento->PROCEDIMENTO_ATIVO = 1;
        $procedimento->save();

        return response($procedimento, 201);
    }

    public function listar()
    {
        $procedimento = Procedimento::where('PROCEDIMENTO_EXCLUSAO', null)
            ->orderBy('PROCEDIMENTO_DESCRICAO')
            ->get();
        return response($procedimento);
    }

    public function search(Request $request)
    {
        $procedimentos = Procedimento::pesquisar($request);
        return response($procedimentos);
    }

    public function buscar(Request $request)
    {
        $procedimento = Procedimento::findOrFail($request->id);

        return response($procedimento);
    }

    public function alterar(ProcedimentoUpdateRequest $request)
    {
        $procedimento = Procedimento::findOrFail($request->PROCEDIMENTO_ID);
        $procedimento->fill($request->post());
        $procedimento->save();;

        return response($procedimento);
    }

    public function deletar(Request $request)
    {
        $procedimento = Procedimento::findOrFail($request->id);
        $procedimento->PROCEDIMENTO_EXCLUSAO = Carbon::now();
        $procedimento->save();

        return response($procedimento);
    }
}
