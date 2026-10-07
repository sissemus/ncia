<?php

namespace App\MyLibs;

class EtapaAtendimentoEnum
{
    const RECEBIDO = 1;
    const CHEGADA_ORIGEM = 2;
    const SAIDA_ORIGEM = 3;
    const CHEGADA_DESTINO = 4;
    const AMBULANCIA_LIBERADA = 5;

    public static function values()
    {
        return [self::RECEBIDO, self::CHEGADA_ORIGEM, self::SAIDA_ORIGEM, self::CHEGADA_DESTINO, self::AMBULANCIA_LIBERADA];
    }

    public static function descricao($etapa)
    {
        return [
            self::RECEBIDO => 'Recebido / em deslocamento',
            self::CHEGADA_ORIGEM => 'Chegada na origem',
            self::SAIDA_ORIGEM => 'Saída da origem',
            self::CHEGADA_DESTINO => 'Chegada no destino',
            self::AMBULANCIA_LIBERADA => 'Ambulância liberada',
        ][(int) $etapa] ?? '-';
    }
}
