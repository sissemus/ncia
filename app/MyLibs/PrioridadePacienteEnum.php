<?php

namespace App\MyLibs;

class PrioridadePacienteEnum
{
    const VERMELHO = 1;
    const LARANJA = 2;
    const AMARELO = 3;
    const VERDE = 4;
    const AZUL = 5;

    public static function values()
    {
        return [
            self::VERMELHO,
            self::LARANJA,
            self::AMARELO,
            self::VERDE,
            self::AZUL,
        ];
    }
}
