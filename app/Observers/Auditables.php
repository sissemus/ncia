<?php

namespace App\Observers;

use App\Models\Acesso;
use App\Models\Aplicacao;
use App\Models\Perfil;
use App\Models\Usuario;
use App\Models\UsuarioPerfil;
use App\Observers\BaseAuditObserver;

class Auditables
{
    public static function register(): void
    {
        // Observadores genéricos
        foreach (self::models() as $model) {
            $model::observe(BaseAuditObserver::class);
        }
    }

    protected static function models(): array
    {
        return [
            Acesso::class,
            Aplicacao::class,
            Perfil::class,
            Usuario::class,
            UsuarioPerfil::class,
        ];
    }
}
