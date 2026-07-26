<?php

namespace App\Http\Controllers;

use App\Http\Requests\Diagnostico\DiagnosticoCreateRequest;
use App\Http\Requests\Diagnostico\DiagnosticoUpdateRequest;
use App\Models\Diagnostico;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DiagnosticoController extends Controller
{
    public function view()
    {
        return view('diagnostico.diagnostico_view');
    }

    public function inserir(DiagnosticoCreateRequest $request)
    {
        $diagnostico = new Diagnostico($request->input());
        $diagnostico->DIAGNOSTICO_ATIVO = 1;
        $diagnostico->save();

        return response($diagnostico, 201);
    }

    public function listar()
    {
        $diagnostico = Diagnostico::where('DIAGNOSTICO_EXCLUSAO', null)
            ->orderBy('DIAGNOSTICO_DESCRICAO')
            ->get();
        return response($diagnostico);
    }

    public function search(Request $request)
    {
        $diagnosticos = Diagnostico::pesquisar($request);
        return response($diagnosticos);
    }

    public function buscar(Request $request)
    {
        $diagnostico = Diagnostico::findOrFail($request->id);

        return response($diagnostico);
    }

    public function alterar(DiagnosticoUpdateRequest $request)
    {
        $diagnostico = Diagnostico::findOrFail($request->DIAGNOSTICO_ID);
        $diagnostico->fill($request->post());
        $diagnostico->save();;

        return response($diagnostico);
    }

    public function deletar(Request $request)
    {
        $diagnostico = Diagnostico::findOrFail($request->id);
        $diagnostico->DIAGNOSTICO_EXCLUSAO = Carbon::now();
        $diagnostico->save();

        return response($diagnostico);
    }
}
