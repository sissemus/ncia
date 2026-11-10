<?php

namespace App\Services;

use App\Models\Chamado;
use App\Models\ChamadoEquipe;
use App\Models\ChamadoSituacao;
use App\Models\Equipe;
use App\Models\Veiculo;
use App\MyLibs\SituacaoChamadoEnum;
use Illuminate\Support\Facades\DB;

class TrocaPlantaoService
{
    public function transferir($veiculoId, $equipeId)
    {
        return DB::transaction(function () use ($veiculoId, $equipeId) {
            $ids = ChamadoEquipe::join('EQUIPE as origem', 'origem.EQUIPE_ID', '=', 'CHAMADO_EQUIPE.EQUIPE_ID')
                ->where('origem.VEICULO_ID', $veiculoId)
                ->where('CHAMADO_EQUIPE_ATIVO', 1)
                ->distinct()->pluck('CHAMADO_ID');

            // O recebimento também bloqueia CHAMADO antes dos vínculos e recursos.
            // Revalidar a situação sob esse bloqueio evita transferir um atendimento iniciado.
            $chamados = Chamado::whereIn('CHAMADO_ID', $ids)
                ->orderBy('CHAMADO_ID')->lockForUpdate()->get();
            $pendentes = collect();
            $equipeIds = collect([(int) $equipeId]);
            foreach ($chamados as $chamado) {
                $situacao = ChamadoSituacao::where('CHAMADO_ID', $chamado->CHAMADO_ID)
                    ->orderByDesc('CHAMADO_SITUACAO_DATA')->orderByDesc('CHAMADO_SITUACAO_ID')
                    ->lockForUpdate()->first();
                if (!$situacao || (int) $situacao->TG_SITUACAO_ID !== SituacaoChamadoEnum::EM_FILA) {
                    continue;
                }
                $vinculos = ChamadoEquipe::where('CHAMADO_ID', $chamado->CHAMADO_ID)
                    ->where('CHAMADO_VIAGEM_ID', $situacao->CHAMADO_VIAGEM_ID)
                    ->where('CHAMADO_EQUIPE_ATIVO', 1)
                    ->orderBy('CHAMADO_EQUIPE_ID')->lockForUpdate()->get();
                $pendentes->push($vinculos);
                $equipeIds = $equipeIds->merge($vinculos->pluck('EQUIPE_ID'));
            }

            $equipes = Equipe::whereIn('EQUIPE_ID', $equipeIds->unique())
                ->orderBy('EQUIPE_ID')->lockForUpdate()->get()->keyBy('EQUIPE_ID');
            $destino = $equipes->get((int) $equipeId);
            abort_unless($destino && (int) $destino->EQUIPE_ATIVO === 1
                && (int) $destino->VEICULO_ID === (int) $veiculoId, 422,
                'Selecione uma equipe ativa da mesma ambulância.');
            abort_unless($destino->equipeProfissional()->where('EQUIPE_PROFISSIONAL_ATIVO', 1)->exists(),
                422, 'A equipe selecionada não possui profissionais ativos.');
            $veiculo = Veiculo::lockForUpdate()->findOrFail($veiculoId);
            abort_unless((int) $veiculo->VEICULO_ATIVO === 1, 422, 'Veículo inativo.');

            $transferidos = 0;
            foreach ($pendentes as $vinculos) {
                // Outro regulador pode ter transferido o chamado antes deste bloqueio.
                if ($vinculos->isEmpty() || $vinculos->contains(function ($vinculo) use ($equipes, $veiculoId) {
                    $origem = $equipes->get($vinculo->EQUIPE_ID);
                    return !$origem || (int) $origem->VEICULO_ID !== (int) $veiculoId;
                })) {
                    continue;
                }
                $anteriores = $vinculos->where('EQUIPE_ID', '!=', (int) $equipeId);
                if ($anteriores->isEmpty()) {
                    continue;
                }
                foreach ($anteriores as $anterior) {
                    $anterior->CHAMADO_EQUIPE_ATIVO = 0;
                    $anterior->save();
                }
                if (!$vinculos->contains('EQUIPE_ID', (int) $equipeId)) {
                    // Usar o mesmo caminho de gravação do encaminhamento preserva
                    // os eventos do modelo e os mecanismos de histórico do banco.
                    ChamadoEquipe::create([
                        'CHAMADO_ID' => $anteriores->first()->CHAMADO_ID,
                        'CHAMADO_VIAGEM_ID' => $anteriores->first()->CHAMADO_VIAGEM_ID,
                        'EQUIPE_ID' => $equipeId,
                        'CHAMADO_EQUIPE_ATIVO' => 1,
                    ]);
                }
                $transferidos++;
            }

            return $transferidos;
        }, 3);
    }
}
