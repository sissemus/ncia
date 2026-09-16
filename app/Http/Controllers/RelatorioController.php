<?php

namespace App\Http\Controllers;

use App\Models\Chamado;
use App\MyLibs\PerfilEnum;
use App\MyLibs\SituacaoChamadoEnum;
use Carbon\Carbon;
use Eltonwebnet\JasperRdr\JasperRdr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class RelatorioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function chamadoEmAtendimento($id)
    {
        abort_unless($this->podeEmitirChamadoEmAtendimento(), 403);

        $chamado = Chamado::with('situacaoAtual')->findOrFail($id);
        abort_unless(
            $chamado->situacaoAtual
                && (int) $chamado->situacaoAtual->TG_SITUACAO_ID === SituacaoChamadoEnum::EM_ATENDIMENTO,
            422,
            'O PDF está disponível somente para chamados em atendimento.'
        );

        Storage::disk('local')->makeDirectory('relatorios');

        $template = resource_path('reports/builds/ChamadoEmAtendimento.jasper');
        abort_unless(File::exists($template), 500, 'O formulário do relatório não foi compilado.');

        $dados = Chamado::getDadosRelatorioChamadoEmAtendimento($id);
        $parametros = [
            'pBrasao' => public_path('img/brasao.png'),
            'pGeradoEm' => Carbon::now('America/Sao_Paulo')->format('d/m/Y H:i:s'),
        ];
        $resultado = JasperRdr::render(
            json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
            $template,
            $parametros,
            'pdf'
        );

        if ($resultado['hasError'] || !File::exists($resultado['report'])) {
            File::delete($resultado['json']);
            File::delete($resultado['report']);
            abort(500, 'Não foi possível gerar o PDF do chamado.');
        }

        return response()
            ->download($resultado['report'], 'Chamado_Atendimento_' . $id . '.pdf')
            ->deleteFileAfterSend(true);
    }

    private function podeEmitirChamadoEmAtendimento()
    {
        $perfis = DB::table('USUARIO_PERFIL')
            ->where('USUARIO_ID', Auth::id())
            ->where('USUARIO_PERFIL_ATIVO', 1)
            ->pluck('PERFIL_ID')
            ->map(function ($perfil) {
                return (int) $perfil;
            });

        return $perfis->intersect([
            PerfilEnum::DESENVOLVEDOR,
            PerfilEnum::ADMINISTRADOR,
            PerfilEnum::REGULADOR_CIA,
            PerfilEnum::EQUIPE_ASSISTENCIAL,
        ])->isNotEmpty();
    }
}
