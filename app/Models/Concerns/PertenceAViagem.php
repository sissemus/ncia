<?php

namespace App\Models\Concerns;

use App\Models\ChamadoViagem;

trait PertenceAViagem
{
    protected static function bootPertenceAViagem()
    {
        static::creating(function ($evento) {
            if (!$evento->CHAMADO_VIAGEM_ID && $evento->CHAMADO_ID) {
                $evento->CHAMADO_VIAGEM_ID = ChamadoViagem::where('CHAMADO_ID', $evento->CHAMADO_ID)
                    ->orderByDesc('NUMERO')->value('CHAMADO_VIAGEM_ID');
            }
        });
    }
}
