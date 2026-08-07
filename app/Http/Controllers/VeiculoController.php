<?php

namespace App\Http\Controllers;

use App\Http\Requests\Veiculo\VeiculoCreateRequest;
use App\Http\Requests\Veiculo\VeiculoUpdateRequest;
use App\Models\Veiculo;
use Illuminate\Http\Request;

class VeiculoController extends Controller
{
    public function view()
    {
        $tiposVeiculo = \App\Models\TabelaGenerica::tipoVeiculo();
        $situacoesVeiculo = \App\Models\TabelaGenerica::situacaoVeiculo();
        return view("veiculo.veiculo_view", compact('tiposVeiculo', 'situacoesVeiculo'));
    }

    public function inserir(VeiculoCreateRequest $request)
    {
        $veiculo = new Veiculo($request->validated());
        $veiculo->save();

        return response($veiculo, 201);
    }

    public function listar()
    {
        $veiculos = Veiculo::where("VEICULO_ATIVO", 1)
            ->orderBy("VEICULO_IDENTIFICACAO")
            ->get();

        return response($veiculos);
    }

    public function pesquisar(Request $request)
    {
        return response(Veiculo::pesquisar($request));
    }

    public function search(Request $request)
    {
        return response(Veiculo::pesquisar($request));
    }

    public function buscar($id)
    {
        return response(Veiculo::buscar($id));
    }

    public function alterar(VeiculoUpdateRequest $request)
    {
        $veiculo = Veiculo::findOrFail($request->VEICULO_ID);
        $veiculo->fill($request->validated());
        $veiculo->save();

        return response($veiculo);
    }

    public function deletar(Request $request)
    {
        $veiculo = Veiculo::findOrFail($request->id);
        $veiculo->VEICULO_ATIVO = 0;
        $veiculo->save();

        return response($veiculo);
    }

    public function alterarSituacao(Request $request)
    {
        $request->validate([
            'VEICULO_ID' => 'required|integer|exists:VEICULO,VEICULO_ID',
            'TG_SITUACAO_VEICULO_ID' => 'required|integer'
        ]);

        $veiculo = Veiculo::findOrFail($request->VEICULO_ID);
        $veiculo->TG_SITUACAO_VEICULO_ID = $request->TG_SITUACAO_VEICULO_ID;
        $veiculo->save();

        return response($veiculo);
    }
}
