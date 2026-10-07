<?php

namespace App\MyLibs;

class SituacaoChamadoEnum
{
    const ABERTO = 1;
    const EM_ANALISE = 2;
    const EM_ATENDIMENTO = 3;
    const CONCLUIDO = 4;
    const CANCELADO = 5;
    const EM_FILA = 6;

    public static function values()
    {
        return [
            self::ABERTO,
            self::EM_ANALISE,
            self::EM_ATENDIMENTO,
            self::CONCLUIDO,
            self::CANCELADO,
            self::EM_FILA,
        ];
    }
}
