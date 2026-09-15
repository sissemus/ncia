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
        if ($value === null || $value === '') {
            return $value;
        }

        $telefone = preg_replace('/[^0-9]/', '', $value);
        $tamanho = strlen($telefone);

        if ($tamanho === 10) {
            return sprintf(
                '(%s) %s-%s',
                substr($telefone, 0, 2),
                substr($telefone, 2, 4),
                substr($telefone, 6, 4)
            );
        }

        if ($tamanho === 11) {
            return sprintf(
                '(%s) %s-%s',
                substr($telefone, 0, 2),
                substr($telefone, 2, 5),
                substr($telefone, 7, 4)
            );
        }

        return $telefone;
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
        if ($value === null || $value === '') {
            return null;
        }

        return preg_replace('/[^0-9]/', '', $value);
    }
}
