<?php

namespace App\Services;

use Carbon\Carbon;

class PlantaoEquipeService
{
    public static function vigente()
    {
        $agora = Carbon::now();
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

    public static function filtrarEncaminhamento($query)
    {
        $hoje = Carbon::now();

        // O relógio ordena as opções; a entrega efetiva é confirmada pelo regulador.
        return $query->where(function ($query) use ($hoje) {
            $query->where(function ($query) use ($hoje) {
                $query->whereDate('EQUIPE_DATA', $hoje->toDateString())
                    ->whereIn('EQUIPE_TURNO', ['SD', 'SN']);
            })->orWhere(function ($query) use ($hoje) {
                self::filtrarPlantao($query, ['turno' => 'SN', 'data' => $hoje->copy()->subDay()->toDateString()]);
            })->orWhere(function ($query) use ($hoje) {
                self::filtrarPlantao($query, ['turno' => 'SD', 'data' => $hoje->copy()->addDay()->toDateString()]);
            });
        });
    }

    public static function classificacao($equipe, array $vigente)
    {
        if (self::corresponde($equipe, $vigente)) {
            return ['ordem' => 0, 'descricao' => 'Turno de referência'];
        }
        if (self::corresponde($equipe, self::seguinte($vigente['turno'], $vigente['data']))) {
            return ['ordem' => 1, 'descricao' => 'Próximo turno'];
        }
        $referencia = $vigente['data'] . ' ' . $vigente['turno'];
        $plantao = $equipe->EQUIPE_DATA->toDateString() . ' ' . $equipe->EQUIPE_TURNO;

        return ['ordem' => 2, 'descricao' => $plantao < $referencia ? 'Plantão anterior' : 'Plantão cadastrado'];
    }

    public static function corresponde($equipe, array $plantao)
    {
        return $equipe->EQUIPE_DATA
            && strtoupper(trim((string) $equipe->EQUIPE_TURNO)) === $plantao['turno']
            && $equipe->EQUIPE_DATA->toDateString() === $plantao['data'];
    }
}
