<?php

namespace App\Services;

use App\Models\Unidade;
use App\MyLibs\PerfilEnum;
use Illuminate\Support\Facades\DB;

class UnidadeSolicitanteService
{
    public static function podeEscolherQualquerUnidade($usuarioId)
    {
        return DB::table('USUARIO_PERFIL')
            ->where('USUARIO_ID', $usuarioId)
            ->where('USUARIO_PERFIL_ATIVO', 1)
            ->whereIn('PERFIL_ID', [PerfilEnum::REGULADOR_CIA, PerfilEnum::ADMINISTRADOR])
            ->exists();
    }

    public static function unidadesPermitidas($usuarioId)
    {
        $query = Unidade::where('UNIDADE_ATIVO', 1)
            ->where('UNIDADE_SOLICITANTE', 1);

        if (!self::podeEscolherQualquerUnidade($usuarioId)) {
            $query->whereIn('UNIDADE_ID', DB::table('USUARIO_UNIDADE')
                ->where('USUARIO_ID', $usuarioId)
                ->select('UNIDADE_ID'));
        }

        return $query;
    }
}
