<?php

namespace App\Http\Controllers;

use App\Models\Chamado;
use App\Models\TabelaGenerica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChamadoAcompanhamentoController extends Controller
{
    public function view()
    {
        $prioridades = TabelaGenerica::prioridadePaciente();
        $situacoesChamado = TabelaGenerica::situacaoChamado();
        $sexos = TabelaGenerica::sexo();
        $tiposChamado = TabelaGenerica::tipoChamado();
        $tiposPrecaucao = TabelaGenerica::tipoPrecaucao();
        $suportesO2 = TabelaGenerica::suporteO2();
        $suportesHemodinamicos = TabelaGenerica::suporteHemodinamico();
        $temperaturas = TabelaGenerica::sinaisVitaisTemperatura();
        $frequenciasCardiacas = TabelaGenerica::sinaisVitaisFrequenciaCardiaca();
        $pressoesArteriais = TabelaGenerica::sinaisVitaisPressaoArterial();
        $saturacoes = TabelaGenerica::sinaisVitaisSaturacao();

        return view("chamado_acompanhamento.chamado_acompanhamento_view", compact(
            'prioridades',
            'situacoesChamado',
            'sexos',
            'tiposChamado',
            'tiposPrecaucao',
            'suportesO2',
            'suportesHemodinamicos',
            'temperaturas',
            'frequenciasCardiacas',
            'pressoesArteriais',
            'saturacoes'
        ));
    }

    public function search(Request $request)
    {
        $userUnidades = DB::table("USUARIO_UNIDADE")
            ->where("USUARIO_ID", Auth::id())
            ->pluck("UNIDADE_ID")
            ->toArray();

        if (empty($userUnidades)) {
            return response()->json([
                'current_page' => 1,
                'data' => [],
                'total' => 0,
                'last_page' => 1
            ]);
        }

        $chamados = Chamado::pesquisarAcompanhamento($request, $userUnidades);

        return response($chamados);
    }

    public function buscar($id)
    {
        $userUnidades = DB::table("USUARIO_UNIDADE")
            ->where("USUARIO_ID", Auth::id())
            ->pluck("UNIDADE_ID")
            ->toArray();

        $chamado = Chamado::with([
            'paciente',
            'unidadeSolicitante',
            'unidadeDestino',
            'procedimentos',
            'diagnosticos',
            'situacoes',
            'situacoes.usuario',
            'situacaoAtual'
        ])->findOrFail($id);

        // Security check: Must belong to user's units
        abort_unless(in_array($chamado->UNIDADE_ID_SOLICITANTE, $userUnidades), 403);

        return response($chamado);
    }
}
