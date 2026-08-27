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
        $permitido = DB::table('USUARIO_PERFIL')->where('USUARIO_ID', $usuarioId)
            ->where('USUARIO_PERFIL_ATIVO', 1)
            ->whereIn('PERFIL_ID', [PerfilEnum::DESENVOLVEDOR, PerfilEnum::ADMINISTRADOR, PerfilEnum::REGULADOR_CIA])
            ->exists();

        if (!$permitido) return $aplicacoes;

        foreach ($aplicacoes as &$aplicacao) {
            if ($aplicacao['APLICACAO_URL'] === 'chamado') {
                $jaExiste = collect($aplicacao['children'])->contains('APLICACAO_URL', 'chamado_analisar');
                if (!$jaExiste) {
                    $aplicacao['children'][] = [
                        'APLICACAO_ID' => -1,
                        'APLICACAO_NOME' => 'Analisar Chamados',
                        'APLICACAO_URL' => 'chamado_analisar',
                        'APLICACAO_ICONE' => 'mdi-clipboard-check-outline',
                    ];
                }
            }
        }
        return $aplicacoes;
    }
}
