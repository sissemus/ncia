<?php

namespace App\Http\Controllers;

use App\Http\Requests\Departamento\DepartamentoCreateRequest;
use App\Http\Requests\Departamento\DepartamentoUpdateRequest;
use App\Models\Departamento;
use App\Models\TabelaGenerica;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DepartamentoController extends Controller
{
    public function view()
    {
        $hierarquias = TabelaGenerica::hierarquia();
        return view('departamento.departamento_view', compact('hierarquias'));
    }

    public function inserir(DepartamentoCreateRequest $request)
    {
        $departamento = new Departamento($request->input());
        $departamento->DEPARTAMENTO_ATIVO = 1;
        $departamento->save();

        return response($departamento, 201);
    }

    public function listar()
    {
        $departamento = Departamento::where('DEPARTAMENTO_EXCLUSAO', null)
            ->orderBy('DEPARTAMENTO_NOME')
            ->get();
        return response($departamento);
    }

    public function search(Request $request)
    {
        $departamentos = Departamento::pesquisar($request);
        return response($departamentos);
    }

    public function buscar(Request $request)
    {
        $departamento = Departamento::with(Departamento::$relacionamento)
            ->findOrFail($request->id);

        return response($departamento);
    }

    public function alterar(DepartamentoUpdateRequest $request)
    {
        $departamento = Departamento::findOrFail($request->DEPARTAMENTO_ID);
        $departamento->fill($request->post());
        $departamento->save();;

        return response($departamento);
    }

    public function deletar(Request $request)
    {
        $departamento = Departamento::findOrFail($request->id);
        $departamento->DEPARTAMENTO_EXCLUSAO = Carbon::now();
        $departamento->save();

        return response($departamento);
    }
}
