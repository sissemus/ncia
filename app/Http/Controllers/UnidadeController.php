<?php

namespace App\Http\Controllers;

use App\Http\Requests\Unidade\UnidadeCreateRequest;
use App\Http\Requests\Unidade\UnidadeUpdateRequest;
use App\Models\Unidade;
use Illuminate\Http\Request;

class UnidadeController extends Controller
{
    public function view()
    {
        return view('unidade.unidade_view');
    }

    public function inserir(UnidadeCreateRequest $request)
    {
        $unidade = new Unidade($request->validated());
        $unidade->save();

        return response($unidade, 201);
    }

    public function listar()
    {
        $unidades = Unidade::orderBy('UNIDADE_NOME')->get();

        return response($unidades);
    }

    public function pesquisar(Request $request)
    {
        $unidades = Unidade::pesquisar($request);

        return response($unidades);
    }

    public function search(Request $request)
    {
        $unidades = Unidade::pesquisar($request);

        return response($unidades);
    }

    public function buscar($id)
    {
        $unidade = Unidade::findOrFail($id);

        return response($unidade);
    }

    public function alterar(UnidadeUpdateRequest $request)
    {
        $unidade = Unidade::findOrFail($request->UNIDADE_ID);

        $unidade->fill($request->validated());
        $unidade->save();

        return response($unidade);
    }

    public function deletar(Request $request)
    {
        $unidade = Unidade::findOrFail($request->id);
        $unidade->delete();

        return response($unidade);
    }
}
