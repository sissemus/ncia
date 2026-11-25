<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Colunas TIME do SQL Server voltam como "07:15:00.0000000"; expõe e grava sempre hh:mm.
 */
class HoraMinuto implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        return $this->formatar($value);
    }

    public function set($model, $key, $value, $attributes)
    {
        return $this->formatar($value);
    }

    private function formatar($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return preg_match('/^(\d{2}:\d{2})/', (string) $value, $resultado) ? $resultado[1] : $value;
    }
}
