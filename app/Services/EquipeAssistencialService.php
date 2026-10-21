<?php

namespace App\Services;

use App\Models\Chamado;
use App\Models\ChamadoEquipe;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EquipeAssistencialService
{
    public function cpfUsuario($usuarioId = null)
    {
        $usuario = Usuario::find($usuarioId ?: Auth::id());
        if (!$usuario) {
            return null;
        }

        $cpf = preg_replace('/[^0-9]/', '', (string) $usuario->USUARIO_CPF);
        return $cpf !== '' ? $cpf : null;
    }

    public function equipeIdsDoUsuario($somenteAtivos = false)
    {
        $cpf = $this->cpfUsuario();
        if (!$cpf) {
            return collect();
        }

        return DB::table('EQUIPE_PROFISSIONAL as ep')
            ->join('PROFISSIONAL as p', 'p.PROFISSIONAL_ID', '=', 'ep.PROFISSIONAL_ID')
            ->join('EQUIPE as e', 'e.EQUIPE_ID', '=', 'ep.EQUIPE_ID')
            ->where('p.PROFISSIONAL_CPF', $cpf)
            ->when($somenteAtivos, function ($query) {
                $query->where('p.PROFISSIONAL_ATIVO', 1)
                    ->where('ep.EQUIPE_PROFISSIONAL_ATIVO', 1)
                    ->where('e.EQUIPE_ATIVO', 1);
            })
            ->distinct()
            ->pluck('ep.EQUIPE_ID')
            ->map(function ($id) {
                return (int) $id;
            });
    }

    public function pertenceEquipe($equipeId, $somenteAtivos = true)
    {
        return $this->equipeIdsDoUsuario($somenteAtivos)->contains((int) $equipeId);
    }

    public function vinculoAtivoDoUsuario(Chamado $chamado, $somenteAtivos = true)
    {
        $equipeIds = $this->equipeIdsDoUsuario($somenteAtivos);

        return ChamadoEquipe::where('CHAMADO_ID', $chamado->CHAMADO_ID)
            ->where('CHAMADO_EQUIPE_ATIVO', 1)
            ->whereIn('EQUIPE_ID', $equipeIds)
            ->orderByDesc('CHAMADO_EQUIPE_ID')
            ->first();
    }

    public function usuarioPodeConsultarChamado(Chamado $chamado)
    {
        $equipeIds = $this->equipeIdsDoUsuario(false);
        if ($equipeIds->isEmpty()) {
            return false;
        }

        return ChamadoEquipe::where('CHAMADO_ID', $chamado->CHAMADO_ID)
            ->whereIn('EQUIPE_ID', $equipeIds)
            ->exists();
    }

    public function filtrarChamadosDoUsuario(Builder $query, $somenteVinculosAtivos = false)
    {
        $equipeIds = $this->equipeIdsDoUsuario($somenteVinculosAtivos);

        if ($equipeIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereExists(function ($sub) use ($equipeIds, $somenteVinculosAtivos) {
            $sub->select(DB::raw(1))
                ->from('CHAMADO_EQUIPE as ce_acesso')
                ->whereColumn('ce_acesso.CHAMADO_ID', 'CHAMADO.CHAMADO_ID')
                ->whereIn('ce_acesso.EQUIPE_ID', $equipeIds)
                ->when($somenteVinculosAtivos, function ($consulta) {
                    $consulta->where('ce_acesso.CHAMADO_EQUIPE_ATIVO', 1);
                });
        });
    }
}
