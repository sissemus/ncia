<?php

namespace App\Http\Controllers;

use App\Http\Requests\VeiculoUnidade\VeiculoUnidadeCreateRequest;
use App\Http\Requests\VeiculoUnidade\VeiculoUnidadeUpdateRequest;
use App\Models\Veiculo;
use App\Models\Unidade;
use App\Models\VeiculoUnidade;
use Illuminate\Http\Request;

class VeiculoUnidadeController extends Controller
{
    public function view()
    {
        // Fetch active vehicles and active units to populate form select options
        $veiculos = Veiculo::where('VEICULO_ATIVO', 1)->orderBy('VEICULO_IDENTIFICACAO')->get();
        $unidades = Unidade::where('UNIDADE_ATIVO', 1)->orderBy('UNIDADE_NOME')->get();

        return view("veiculo_unidade.veiculo_unidade_view", compact('veiculos', 'unidades'));
    }

    public function inserir(VeiculoUnidadeCreateRequest $request)
    {
        $vinculo = VeiculoUnidade::vincular($request->VEICULO_ID, $request->UNIDADE_ID);
        return response($vinculo, 201);
    }

    public function alterar(VeiculoUnidadeUpdateRequest $request)
    {
        // Historicamente, alterar o vínculo significa fechar o atual e criar um novo
        $vinculo = VeiculoUnidade::vincular($request->VEICULO_ID, $request->UNIDADE_ID);
        return response($vinculo);
    }

    public function search(Request $request)
    {
        return response(VeiculoUnidade::pesquisar($request));
    }

    public function buscar($id)
    {
        return response(VeiculoUnidade::with(VeiculoUnidade::relacionamento())->findOrFail($id));
    }

    public function desvincular(Request $request)
    {
        $request->validate([
            "VEICULO_ID" => ["required", "integer", "exists:VEICULO,VEICULO_ID"],
        ]);

        VeiculoUnidade::desvincular($request->VEICULO_ID);
        return response(["message" => "Veículo desvinculado com sucesso."]);
    }

    public function deletar(Request $request)
    {
        // Deletar um vínculo na verdade desvincula (fecha o período definindo a DT_FIM como agora)
        $vinculo = VeiculoUnidade::findOrFail($request->id);
        $vinculo->VEICULO_UNIDADE_DT_FIM = now();
        $vinculo->save();
        return response($vinculo);
    }
}
