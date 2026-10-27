<?php

namespace App\Http\Middleware;

use App\Models\Acesso;
use App\MyLibs\PerfilEnum;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AutorizarAplicacao
{
    public function handle(Request $request, Closure $next, $url, $consultaEquipe = null)
    {
        $usuarioId = auth()->id();
        $autorizado = Acesso::usuarioPossuiAplicacao($usuarioId, $url);

        if (!$autorizado && $consultaEquipe === 'consulta_equipe') {
            $perfis = DB::table('USUARIO_PERFIL')
                ->where('USUARIO_ID', $usuarioId)
                ->where('USUARIO_PERFIL_ATIVO', 1)
                ->pluck('PERFIL_ID');

            $autorizado = $perfis->contains(PerfilEnum::EQUIPE_ASSISTENCIAL)
                && $perfis->intersect([
                    PerfilEnum::DESENVOLVEDOR,
                    PerfilEnum::ADMINISTRADOR,
                    PerfilEnum::REGULADOR_CIA,
                ])->isEmpty()
                && Acesso::usuarioPossuiAplicacao($usuarioId, 'chamado_acompanhamento');
        }

        abort_unless($autorizado, 403);

        return $next($request);
    }
}
