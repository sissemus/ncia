<?php

namespace App\Services;

use App\Models\TabelaGenerica;
use App\MyLibs\RTG;

class TipoChamadoService
{
    private static $ids = [];

    public static function id($descricao)
    {
        if (isset(self::$ids[$descricao])) return self::$ids[$descricao];
        $tipo = TabelaGenerica::tipoChamado()->first(function ($item) use ($descricao) {
            return mb_strtolower(trim($item->DESCRICAO), 'UTF-8') === mb_strtolower($descricao, 'UTF-8');
        });

        abort_unless($tipo, 422, 'Tipo de chamado não cadastrado: ' . $descricao);
        self::$ids[$descricao] = (int) $tipo->COLUNA_ID;
        return self::$ids[$descricao];
    }

    public static function retorno($id)
    {
        return (int) $id === self::id('Retorno');
    }

    public static function retornoImediato($id)
    {
        return (int) $id === self::id('Retorno imediato');
    }
}
