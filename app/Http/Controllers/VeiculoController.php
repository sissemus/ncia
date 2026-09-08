<?php

namespace App\Http\Controllers;

use App\Http\Requests\Veiculo\VeiculoCreateRequest;
use App\Http\Requests\Veiculo\VeiculoUpdateRequest;
use App\Models\Veiculo;
use App\Models\VeiculoUnidade;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VeiculoController extends Controller
{
    public function view()
    {
        $tiposVeiculo = \App\Models\TabelaGenerica::tipoVeiculo();
        $situacoesVeiculo = \App\Models\TabelaGenerica::situacaoVeiculo();
        $unidades = \App\Models\Unidade::where('UNIDADE_ATIVO', 1)->orderBy('UNIDADE_NOME')->get();
        return view("veiculo.veiculo_view", compact('tiposVeiculo', 'situacoesVeiculo', 'unidades'));
    }

    public function inserir(VeiculoCreateRequest $request)
    {
        $veiculo = new Veiculo($request->validated());
        $veiculo->save();

        if ($request->filled('UNIDADE_ID')) {
            $dtIni = $request->VEICULO_UNIDADE_DT_INI ? Carbon::parse($request->VEICULO_UNIDADE_DT_INI) : Carbon::now();
            VeiculoUnidade::create([
                'VEICULO_ID' => $veiculo->VEICULO_ID,
                'UNIDADE_ID' => $request->UNIDADE_ID,
                'VEICULO_IDENTIFICACAO' => mb_strtoupper($veiculo->VEICULO_IDENTIFICACAO, 'UTF-8'),
                'VEICULO_UNIDADE_DT_INI' => $dtIni,
                'VEICULO_UNIDADE_DT_FIM' => null
            ]);
        }

        return response($veiculo->load(Veiculo::relacionamento()), 201);
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
        $veiculo->VEICULO_IDENTIFICACAO = mb_strtoupper($veiculo->VEICULO_IDENTIFICACAO, 'UTF-8');
        
        $veiculo->save();

        // Handle unit mapping
        $vinculoAtivo = VeiculoUnidade::ondeAtivo($veiculo->VEICULO_ID)->first();

        if ($request->filled('UNIDADE_ID')) {
            $novaUnidadeId = (int) $request->UNIDADE_ID;
            $dtIni = $request->VEICULO_UNIDADE_DT_INI ? Carbon::parse($request->VEICULO_UNIDADE_DT_INI) : Carbon::now();

            if ($vinculoAtivo && $vinculoAtivo->UNIDADE_ID !== $novaUnidadeId) {
                $novaData = $dtIni->format('Y-m-d');
                $dataAntiga = $vinculoAtivo->VEICULO_UNIDADE_DT_INI->format('Y-m-d');

                if ($novaData === $dataAntiga) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'VEICULO_UNIDADE_DT_INI' => ['A data do vínculo deve ser alterada quando a unidade vinculada ao veículo for alterada.']
                    ]);
                }

                if ($novaData < $dataAntiga) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'VEICULO_UNIDADE_DT_INI' => ['A data do novo vínculo não pode ser anterior à data do vínculo anterior.']
                    ]);
                }
            }

            if (!$vinculoAtivo) {
                // No active mapping: create a new one
                VeiculoUnidade::create([
                    'VEICULO_ID' => $veiculo->VEICULO_ID,
                    'UNIDADE_ID' => $novaUnidadeId,
                    'VEICULO_UNIDADE_DT_INI' => $dtIni,
                    'VEICULO_UNIDADE_DT_FIM' => null
                ]);
            } else if ($vinculoAtivo->UNIDADE_ID !== $novaUnidadeId) {
                // Unit changed: close old mapping and create a new one
                $vinculoAtivo->VEICULO_UNIDADE_DT_FIM = $dtIni->copy()->subDay();
                $vinculoAtivo->save();

                VeiculoUnidade::create([
                    'VEICULO_ID' => $veiculo->VEICULO_ID,
                    'UNIDADE_ID' => $novaUnidadeId,
                    'VEICULO_UNIDADE_DT_INI' => $dtIni,
                    'VEICULO_UNIDADE_DT_FIM' => null
                ]);
            } else {
                // Unit is the same: update DT_INI if changed
                $vinculoAtivo->VEICULO_UNIDADE_DT_INI = $dtIni;
                $vinculoAtivo->save();
            }
        } else {
            // UNIDADE_ID is empty: close any active mapping
            if ($vinculoAtivo) {
                $vinculoAtivo->VEICULO_UNIDADE_DT_FIM = Carbon::now();
                $vinculoAtivo->save();
            }
        }

        return response($veiculo->load(Veiculo::relacionamento()));
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
