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
        return view("unidade.unidade_view");
    }

    public function inserir(UnidadeCreateRequest $request)
    {
        $unidade = new Unidade($request->validated());
        $unidade->save();

        return response($unidade, 201);
    }

    public function listar()
    {
        $unidades = Unidade::where("UNIDADE_ATIVO", 1)
            ->orderBy("UNIDADE_NOME")
            ->get();

        return response($unidades);
    }

    public function pesquisar(Request $request)
    {
        return response(Unidade::pesquisar($request));
    }

    public function search(Request $request)
    {
        return response(Unidade::pesquisar($request));
    }

    public function buscar($id)
    {
        return response(Unidade::findOrFail($id));
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
        $unidade->UNIDADE_ATIVO = 0;
        $unidade->save();

        return response($unidade);
    }
}
