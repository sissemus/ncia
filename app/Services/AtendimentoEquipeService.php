<?php

namespace App\Services;

use App\Models\Chamado;
use App\Models\ChamadoAtendimentoEtapa;
use App\Models\ChamadoEquipe;
use App\Models\ChamadoSituacao;
use App\Models\Equipe;
use App\Models\Veiculo;
use App\MyLibs\EtapaAtendimentoEnum;
use App\MyLibs\SituacaoChamadoEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AtendimentoEquipeService
{
    private $acessoEquipe;

    public function __construct(EquipeAssistencialService $acessoEquipe)
    {
        $this->acessoEquipe = $acessoEquipe;
    }

    public function listarFilaDoUsuario()
    {
        $equipeIds = $this->acessoEquipe->equipeIdsDoUsuario(true);
        if ($equipeIds->isEmpty()) {
            return collect();
        }

        $query = Chamado::with([
            'paciente', 'unidadeSolicitante', 'unidadeDestino', 'situacaoAtual',
            'etapasAtendimento.usuario', 'vinculosEquipe.equipe.veiculo',
        ])
            ->join('CHAMADO_EQUIPE as ce_fila', 'ce_fila.CHAMADO_ID', '=', 'CHAMADO.CHAMADO_ID')
            ->where('ce_fila.CHAMADO_EQUIPE_ATIVO', 1)
            ->whereIn('ce_fila.EQUIPE_ID', $equipeIds)
            ->select('CHAMADO.*', 'ce_fila.EQUIPE_ID as EQUIPE_FILA_ID', 'ce_fila.CHAMADO_EQUIPE_ID');

        $this->restringirSituacaoAtual($query, [
            SituacaoChamadoEnum::EM_FILA,
            SituacaoChamadoEnum::EM_ATENDIMENTO,
        ]);

        return $query
            ->orderByRaw('CASE WHEN EXISTS (
                SELECT 1 FROM CHAMADO_SITUACAO cs_ord
                WHERE cs_ord.CHAMADO_ID = CHAMADO.CHAMADO_ID
                  AND cs_ord.TG_SITUACAO_ID = ?
                  AND NOT EXISTS (
                      SELECT 1 FROM CHAMADO_SITUACAO cs_ord2
                      WHERE cs_ord2.CHAMADO_ID = cs_ord.CHAMADO_ID
                        AND (cs_ord2.CHAMADO_SITUACAO_DATA > cs_ord.CHAMADO_SITUACAO_DATA
                          OR (cs_ord2.CHAMADO_SITUACAO_DATA = cs_ord.CHAMADO_SITUACAO_DATA
                            AND cs_ord2.CHAMADO_SITUACAO_ID > cs_ord.CHAMADO_SITUACAO_ID))
                  )
            ) THEN 0 ELSE 1 END', [SituacaoChamadoEnum::EM_ATENDIMENTO])
            ->orderBy('ce_fila.EQUIPE_ID')
            ->orderBy('CHAMADO.TG_PRIORIDADE_ID')
            ->orderBy('CHAMADO.CHAMADO_DATA')
            ->orderBy('CHAMADO.CHAMADO_ID')
            ->get()
            ->map(function ($chamado) {
                $ultima = $chamado->etapasAtendimento->last();
                $proxima = $ultima ? (int) $ultima->ATENDIMENTO_ETAPA_ID + 1 : EtapaAtendimentoEnum::RECEBIDO;
                $chamado->setAttribute('ETAPA_ATUAL_ID', $ultima ? (int) $ultima->ATENDIMENTO_ETAPA_ID : null);
                $chamado->setAttribute('ETAPA_ATUAL_DESCRICAO', $ultima ? $ultima->ATENDIMENTO_ETAPA_DESCRICAO : 'Aguardando recebimento');
                $chamado->setAttribute('PROXIMA_ETAPA_ID', $proxima <= EtapaAtendimentoEnum::AMBULANCIA_LIBERADA ? $proxima : null);
                $chamado->setAttribute('PROXIMA_ETAPA_DESCRICAO', $proxima <= EtapaAtendimentoEnum::AMBULANCIA_LIBERADA
                    ? EtapaAtendimentoEnum::descricao($proxima) : null);

                return $chamado;
            });
    }

    public function avancar($chamadoId, $etapaEsperadaId)
    {
        return DB::transaction(function () use ($chamadoId, $etapaEsperadaId) {
            $chamado = Chamado::lockForUpdate()->findOrFail($chamadoId);
            $situacao = $this->situacaoAtual($chamado->CHAMADO_ID, true);
            abort_unless($situacao && in_array((int) $situacao->TG_SITUACAO_ID, [
                SituacaoChamadoEnum::EM_FILA,
                SituacaoChamadoEnum::EM_ATENDIMENTO,
            ], true), 422, 'O chamado não está disponível para avanço do atendimento.');

            $vinculo = ChamadoEquipe::where('CHAMADO_ID', $chamado->CHAMADO_ID)
                ->where('CHAMADO_EQUIPE_ATIVO', 1)
                ->orderByDesc('CHAMADO_EQUIPE_ID')
                ->lockForUpdate()
                ->first();
            abort_unless($vinculo, 422, 'O chamado não possui equipe ativa vinculada.');
            abort_unless($this->acessoEquipe->pertenceEquipe($vinculo->EQUIPE_ID, true), 403);

            $equipe = Equipe::lockForUpdate()->findOrFail($vinculo->EQUIPE_ID);
            $veiculo = Veiculo::lockForUpdate()->findOrFail($equipe->VEICULO_ID);
            $ultimaEtapa = ChamadoAtendimentoEtapa::where('CHAMADO_ID', $chamado->CHAMADO_ID)
                ->orderByDesc('ATENDIMENTO_ETAPA_ID')
                ->lockForUpdate()
                ->first();
            $proximaEtapa = $ultimaEtapa
                ? (int) $ultimaEtapa->ATENDIMENTO_ETAPA_ID + 1
                : EtapaAtendimentoEnum::RECEBIDO;

            abort_unless(in_array($proximaEtapa, EtapaAtendimentoEnum::values(), true), 422, 'Todas as etapas já foram registradas.');
            abort_unless($proximaEtapa === (int) $etapaEsperadaId, 422,
                'A etapa do atendimento já foi atualizada. Recarregue a fila antes de continuar.');

            if ($proximaEtapa === EtapaAtendimentoEnum::RECEBIDO) {
                if ((int) $situacao->TG_SITUACAO_ID === SituacaoChamadoEnum::EM_FILA) {
                    abort_unless($this->primeiroChamadoEmFila($vinculo->EQUIPE_ID) === (int) $chamado->CHAMADO_ID, 422,
                        'Existe outro chamado com maior prioridade ou mais antigo aguardando recebimento.');
                    abort_if($this->existeOutroEmAtendimento($vinculo->EQUIPE_ID, $chamado->CHAMADO_ID), 422,
                        'A equipe já possui um chamado em atendimento.');
                    abort_unless((int) $veiculo->VEICULO_ATIVO === 1 && (int) $veiculo->TG_SITUACAO_VEICULO_ID === 1, 422,
                        'O veículo não está disponível para iniciar o atendimento.');
                }
            } else {
                abort_unless((int) $situacao->TG_SITUACAO_ID === SituacaoChamadoEnum::EM_ATENDIMENTO, 422,
                    'A situação do chamado foi alterada.');
            }

            ChamadoAtendimentoEtapa::create([
                'CHAMADO_ID' => $chamado->CHAMADO_ID,
                'CHAMADO_EQUIPE_ID' => $vinculo->CHAMADO_EQUIPE_ID,
                'ATENDIMENTO_ETAPA_ID' => $proximaEtapa,
                'ATENDIMENTO_ETAPA_DATA' => Carbon::now('America/Sao_Paulo'),
                'USUARIO_ID' => Auth::id(),
            ]);

            if ($proximaEtapa === EtapaAtendimentoEnum::RECEBIDO
                && (int) $situacao->TG_SITUACAO_ID === SituacaoChamadoEnum::EM_FILA) {
                $veiculo->TG_SITUACAO_VEICULO_ID = 2;
                $veiculo->save();
                $this->registrarSituacao($chamado, SituacaoChamadoEnum::EM_ATENDIMENTO, 'Atendimento recebido pela equipe assistencial.');
            }

            if ($proximaEtapa === EtapaAtendimentoEnum::AMBULANCIA_LIBERADA) {
                $this->registrarSituacao($chamado, SituacaoChamadoEnum::CONCLUIDO, 'Ambulância liberada pela equipe assistencial.');
                $vinculo->CHAMADO_EQUIPE_ATIVO = 0;
                $vinculo->save();
                $veiculo->TG_SITUACAO_VEICULO_ID = 1;
                $veiculo->save();
            }

            return [
                'etapa' => $proximaEtapa,
                'descricao' => EtapaAtendimentoEnum::descricao($proximaEtapa),
            ];
        });
    }

    private function primeiroChamadoEmFila($equipeId)
    {
        $query = Chamado::join('CHAMADO_EQUIPE as ce_ordem', 'ce_ordem.CHAMADO_ID', '=', 'CHAMADO.CHAMADO_ID')
            ->where('ce_ordem.EQUIPE_ID', $equipeId)
            ->where('ce_ordem.CHAMADO_EQUIPE_ATIVO', 1)
            ->select('CHAMADO.CHAMADO_ID');
        $this->restringirSituacaoAtual($query, [SituacaoChamadoEnum::EM_FILA]);

        return (int) $query->orderBy('CHAMADO.TG_PRIORIDADE_ID')
            ->orderBy('CHAMADO.CHAMADO_DATA')
            ->orderBy('CHAMADO.CHAMADO_ID')
            ->lockForUpdate()
            ->value('CHAMADO.CHAMADO_ID');
    }

    private function existeOutroEmAtendimento($equipeId, $ignorarChamadoId)
    {
        $query = Chamado::join('CHAMADO_EQUIPE as ce_ocupada', 'ce_ocupada.CHAMADO_ID', '=', 'CHAMADO.CHAMADO_ID')
            ->where('ce_ocupada.EQUIPE_ID', $equipeId)
            ->where('ce_ocupada.CHAMADO_EQUIPE_ATIVO', 1)
            ->where('CHAMADO.CHAMADO_ID', '!=', $ignorarChamadoId);
        $this->restringirSituacaoAtual($query, [SituacaoChamadoEnum::EM_ATENDIMENTO]);

        return $query->exists();
    }

    private function restringirSituacaoAtual($query, array $situacoes)
    {
        return $query->whereExists(function ($sub) use ($situacoes) {
            $sub->select(DB::raw(1))
                ->from('CHAMADO_SITUACAO as cs_atual')
                ->whereColumn('cs_atual.CHAMADO_ID', 'CHAMADO.CHAMADO_ID')
                ->whereIn('cs_atual.TG_SITUACAO_ID', $situacoes)
                ->whereNotExists(function ($maisNova) {
                    $maisNova->select(DB::raw(1))
                        ->from('CHAMADO_SITUACAO as cs_nova')
                        ->whereColumn('cs_nova.CHAMADO_ID', 'cs_atual.CHAMADO_ID')
                        ->where(function ($ordem) {
                            $ordem->whereColumn('cs_nova.CHAMADO_SITUACAO_DATA', '>', 'cs_atual.CHAMADO_SITUACAO_DATA')
                                ->orWhere(function ($empate) {
                                    $empate->whereColumn('cs_nova.CHAMADO_SITUACAO_DATA', '=', 'cs_atual.CHAMADO_SITUACAO_DATA')
                                        ->whereColumn('cs_nova.CHAMADO_SITUACAO_ID', '>', 'cs_atual.CHAMADO_SITUACAO_ID');
                                });
                        });
                });
        });
    }

    private function situacaoAtual($chamadoId, $bloquear = false)
    {
        $query = ChamadoSituacao::where('CHAMADO_ID', $chamadoId)
            ->orderByDesc('CHAMADO_SITUACAO_DATA')
            ->orderByDesc('CHAMADO_SITUACAO_ID');
        if ($bloquear) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function registrarSituacao(Chamado $chamado, $situacao, $observacao)
    {
        ChamadoSituacao::create([
            'CHAMADO_ID' => $chamado->CHAMADO_ID,
            'TG_SITUACAO_ID' => $situacao,
            'CHAMADO_SITUACAO_DATA' => Carbon::now('America/Sao_Paulo'),
            'CHAMADO_SITUACAO_OBSERVACAO' => $observacao,
            'USUARIO_ID' => Auth::id(),
        ]);
    }
}
