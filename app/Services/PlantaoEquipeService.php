<?php

namespace App\Services;

use Carbon\Carbon;

class PlantaoEquipeService
{
    public static function vigente()
    {
        $agora = Carbon::now('America/Sao_Paulo');
        if ($agora->hour < 7) {
            return ['turno' => 'SN', 'data' => $agora->subDay()->toDateString()];
        }

        return ['turno' => $agora->hour < 19 ? 'SD' : 'SN', 'data' => $agora->toDateString()];
    }

    public static function seguinte($turno, $data)
    {
        $turno = strtoupper(trim((string) $turno));
        abort_unless(in_array($turno, ['SD', 'SN'], true) && $data, 422,
            'A equipe de origem deve possuir data e turno SD ou SN válidos.');
        $data = Carbon::parse($data);

        return ['turno' => $turno === 'SD' ? 'SN' : 'SD',
            'data' => ($turno === 'SN' ? $data->addDay() : $data)->toDateString()];
    }

    public static function filtrarPlantao($query, array $plantao)
    {
        return $query->where('EQUIPE_TURNO', $plantao['turno'])
            ->whereDate('EQUIPE_DATA', $plantao['data']);
    }

    public static function filtrarEncaminhamento($query, array $vigente)
    {
        $seguinte = self::seguinte($vigente['turno'], $vigente['data']);

        return $query->where(function ($query) use ($vigente, $seguinte) {
            $query->where(function ($query) use ($vigente) {
                self::filtrarPlantao($query, $vigente);
            })->orWhere(function ($query) use ($seguinte) {
                self::filtrarPlantao($query, $seguinte);
            });
        });
    }

    public static function corresponde($equipe, array $plantao)
    {
        return $equipe->EQUIPE_DATA
            && strtoupper(trim((string) $equipe->EQUIPE_TURNO)) === $plantao['turno']
            && $equipe->EQUIPE_DATA->toDateString() === $plantao['data'];
    }
}
