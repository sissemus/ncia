<?php

namespace App\Http\Middleware;

use App\Models\Acesso;
use App\Models\Usuario;
use App\MyLibs\PerfilEnum;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class CompartilharVariaveis {
        public function handle(Request $request, Closure $next) {
        View::share([
            'usuario' => Usuario::getById(auth()->id()),
            "aplicacoes" => $this->aplicacoes(auth()->id())
        ]);
        return $next($request);
    }

    private function aplicacoes($usuarioId)
    {
        $aplicacoes = Acesso::getByUsuarioId($usuarioId) ?: [];
        foreach ($aplicacoes as $indice => &$aplicacao) {
            if (!empty($aplicacao['children'])) {
                $aplicacao['children'] = array_values($aplicacao['children']);
            }
        }
        unset($aplicacao);
        $aplicacoes = array_values($aplicacoes);
        $perfis = DB::table('USUARIO_PERFIL')->where('USUARIO_ID', $usuarioId)
            ->where('USUARIO_PERFIL_ATIVO', 1)
            ->pluck('PERFIL_ID');
        $podeAnalisar = $perfis->intersect([
            PerfilEnum::DESENVOLVEDOR,
            PerfilEnum::ADMINISTRADOR,
            PerfilEnum::REGULADOR_CIA,
        ])->isNotEmpty();
        $podeAcompanhar = $perfis->intersect([
            PerfilEnum::DESENVOLVEDOR,
            PerfilEnum::ADMINISTRADOR,
            PerfilEnum::REGULADOR_CIA,
            PerfilEnum::UNIDADE,
            PerfilEnum::EQUIPE_ASSISTENCIAL,
        ])->isNotEmpty();

        foreach ($aplicacoes as &$aplicacao) {
            if ($aplicacao['APLICACAO_URL'] === 'chamado') {
                $aplicacao['children'] = array_values(array_filter(
                    $aplicacao['children'],
                    function ($child) use ($podeAnalisar, $podeAcompanhar) {
                        if ($child['APLICACAO_URL'] === 'chamado_analisar') return $podeAnalisar;
                        if ($child['APLICACAO_URL'] === 'chamado_acompanhamento') return $podeAcompanhar;
                        return true;
                    }
                ));
                $children = collect($aplicacao['children']);
                if ($podeAnalisar && !$children->contains('APLICACAO_URL', 'chamado_analisar')) {
                    $aplicacao['children'][] = [
                        'APLICACAO_ID' => -1,
                        'APLICACAO_NOME' => 'Analisar Chamados',
                        'APLICACAO_URL' => 'chamado_analisar',
                        'APLICACAO_ICONE' => 'mdi-clipboard-check-outline',
                    ];
                }
                if ($podeAcompanhar && !$children->contains('APLICACAO_URL', 'chamado_acompanhamento')) {
                    $aplicacao['children'][] = [
                        'APLICACAO_ID' => -2,
                        'APLICACAO_NOME' => 'Acompanhamento de Chamados',
                        'APLICACAO_URL' => 'chamado_acompanhamento',
                        'APLICACAO_ICONE' => 'mdi-clipboard-list-outline',
                    ];
                }
            }
        }
        return $aplicacoes;
    }
}
