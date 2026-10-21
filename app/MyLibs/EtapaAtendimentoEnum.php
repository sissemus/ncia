<?php

namespace App\MyLibs;

class EtapaAtendimentoEnum
{
    const RECEBIDO = 1;
    const CHEGADA_ORIGEM = 2;
    const SAIDA_ORIGEM = 3;
    const CHEGADA_DESTINO = 4;
    const AMBULANCIA_LIBERADA = 5;
    const AGUARDANDO_PACIENTE = 6;
    const SAIDA_DESTINO = 7;
    const CHEGADA_ORIGEM_RETORNO = 8;
    const AMBULANCIA_LIBERADA_RETORNO = 9;

    public static function values()
    {
        return [
            self::RECEBIDO, self::CHEGADA_ORIGEM, self::SAIDA_ORIGEM,
            self::CHEGADA_DESTINO, self::AMBULANCIA_LIBERADA,
            self::AGUARDANDO_PACIENTE, self::SAIDA_DESTINO,
            self::CHEGADA_ORIGEM_RETORNO, self::AMBULANCIA_LIBERADA_RETORNO,
        ];
    }

    public static function proxima($ultima, $retornoImediato)
    {
        if ($ultima === null) return self::RECEBIDO;
        if ((int) $ultima === self::CHEGADA_DESTINO) {
            return $retornoImediato ? self::AGUARDANDO_PACIENTE : self::AMBULANCIA_LIBERADA;
        }
        if ($retornoImediato && (int) $ultima >= self::AGUARDANDO_PACIENTE
            && (int) $ultima < self::AMBULANCIA_LIBERADA_RETORNO) {
            return (int) $ultima + 1;
        }
        if ((int) $ultima >= self::AMBULANCIA_LIBERADA) return null;
        return (int) $ultima + 1;
    }

    public static function liberacao($retornoImediato)
    {
        return $retornoImediato ? self::AMBULANCIA_LIBERADA_RETORNO : self::AMBULANCIA_LIBERADA;
    }

    public static function descricao($etapa)
    {
        return [
            self::RECEBIDO => 'Recebido / em deslocamento',
            self::CHEGADA_ORIGEM => 'Chegada na origem',
            self::SAIDA_ORIGEM => 'Saída da origem',
            self::CHEGADA_DESTINO => 'Chegada no destino',
            self::AMBULANCIA_LIBERADA => 'Ambulância liberada',
            self::AGUARDANDO_PACIENTE => 'Aguardando paciente',
            self::SAIDA_DESTINO => 'Saída do destino com paciente',
            self::CHEGADA_ORIGEM_RETORNO => 'Chegada à unidade de origem',
            self::AMBULANCIA_LIBERADA_RETORNO => 'Ambulância liberada',
        ][(int) $etapa] ?? '-';
    }
}
