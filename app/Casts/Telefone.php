<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Telefone implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        if ($value === null) {
            return '';
        }

        // Adiciona zeros à esquerda para garantir que a string tenha 11 caracteres
        $telefone = str_pad($value, 11, '0', STR_PAD_LEFT);

        $ddd    = substr($telefone, 0, 2);
        $prefixo = substr($telefone, 2, 5);
        $sufixo  = substr($telefone, 7, 4);

        $telefone_formatado = "($ddd) $prefixo-$sufixo";

        return $telefone_formatado;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        if ($value == null) {
            return null;
        }

        $telefone = preg_replace('/[^0-9]/', '', $value);

        return $telefone;
    }
}
