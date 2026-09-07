<?php

namespace App\Services;

use App\Models\Chamado;
use App\Models\ChamadoEquipe;
use App\Models\ChamadoSituacao;
use App\Models\Cancelamento;
use App\Models\Equipe;
use App\Models\Veiculo;
use App\MyLibs\RTG;
use App\MyLibs\SituacaoChamadoEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChamadoFluxoService
{
    private const MOTIVO_PRAZO_EXCEDIDO = 'PRAZO DE 24 HORAS EXCEDIDO';

    public function recepcionar(Chamado $chamado)
    {
        $this->validarSituacao($chamado, [SituacaoChamadoEnum::ABERTO]);
        $this->registrarSituacao($chamado, SituacaoChamadoEnum::EM_ANALISE);
    }

    public function encaminhar(Chamado $chamado, $equipeId)
    {
        $this->validarSituacao($chamado, [SituacaoChamadoEnum::EM_ANALISE]);
        $hoje = Carbon::now('America/Sao_Paulo')->toDateString();

        $equipe = Equipe::where('EQUIPE_ID', $equipeId)
            ->whereDate('EQUIPE_DATA', $hoje)
            ->where('EQUIPE_ATIVO', 1)
            ->whereHas('equipeProfissional', function ($query) {
                $query->where('EQUIPE_PROFISSIONAL_ATIVO', 1);
            })
            ->lockForUpdate()
            ->firstOrFail();

        $veiculo = Veiculo::lockForUpdate()->findOrFail($equipe->VEICULO_ID);
        abort_unless(
            (int) $veiculo->VEICULO_ATIVO === 1 && (int) $veiculo->TG_SITUACAO_VEICULO_ID === 1,
            422,
            'Veículo indisponível.'
        );
        abort_if(
            ChamadoEquipe::where('EQUIPE_ID', $equipe->EQUIPE_ID)
                ->where('CHAMADO_EQUIPE_ATIVO', 1)
                ->lockForUpdate()
                ->exists(),
            422,
            'Equipe já está vinculada a outro chamado.'
        );

        ChamadoEquipe::create([
            'CHAMADO_ID' => $chamado->CHAMADO_ID,
            'EQUIPE_ID' => $equipe->EQUIPE_ID,
            'CHAMADO_EQUIPE_ATIVO' => 1,
        ]);

        $veiculo->TG_SITUACAO_VEICULO_ID = 2;
        $veiculo->save();
        $this->registrarSituacao($chamado, SituacaoChamadoEnum::EM_ATENDIMENTO);
    }

    public function cancelarAnalise(Chamado $chamado, $motivoId, $motivacao)
    {
        $this->validarSituacao($chamado, [SituacaoChamadoEnum::EM_ANALISE]);
        $this->registrarCancelamento($chamado, $motivoId, $motivacao);
    }

    public function cancelarAtendimento(Chamado $chamado, $motivoId, $motivacao)
    {
        $this->validarSituacao($chamado, [SituacaoChamadoEnum::EM_ATENDIMENTO]);
        $vinculos = $this->bloquearRecursosAtivos($chamado);
        $this->registrarCancelamento($chamado, $motivoId, $motivacao);
        $this->liberarRecursos($vinculos);
    }

    public function cancelarPorPrazoExcedido(Chamado $chamado)
    {
        $this->validarSituacao($chamado, [SituacaoChamadoEnum::ABERTO]);

        $abertura = Carbon::parse(
            $chamado->getRawOriginal('CHAMADO_DATA'),
            'America/Sao_Paulo'
        );
        $limite = Carbon::now('America/Sao_Paulo')->subHours(24);

        abort_if($abertura->gt($limite), 422, 'O chamado ainda não completou 24 horas em aberto.');

        $motivoId = DB::table('TABELA_GENERICA')
            ->where('TABELA_ID', RTG::MOTIVO_CANCELAMENTO)
            ->where('DESCRICAO', self::MOTIVO_PRAZO_EXCEDIDO)
            ->where('ATIVO', 1)
            ->value('COLUNA_ID');

        abort_unless($motivoId, 422, 'Motivo de cancelamento por prazo excedido não cadastrado.');

        $this->registrarCancelamento(
            $chamado,
            $motivoId,
            'Chamado cancelado manualmente por permanecer aberto por mais de 24 horas.'
        );
    }

    public function concluirAtendimento(Chamado $chamado)
    {
        $this->validarSituacao($chamado, [SituacaoChamadoEnum::EM_ATENDIMENTO]);
        $vinculos = $this->bloquearRecursosAtivos($chamado);
        $this->registrarSituacao(
            $chamado,
            SituacaoChamadoEnum::CONCLUIDO,
            'Transporte concluído sem intercorrência.'
        );
        $this->liberarRecursos($vinculos);
    }

    public function validarSituacao(Chamado $chamado, array $situacoesPermitidas)
    {
        $situacaoAtual = ChamadoSituacao::where('CHAMADO_ID', $chamado->CHAMADO_ID)
            ->orderByDesc('CHAMADO_SITUACAO_DATA')
            ->orderByDesc('CHAMADO_SITUACAO_ID')
            ->lockForUpdate()
            ->first();

        abort_unless(
            $situacaoAtual && in_array((int) $situacaoAtual->TG_SITUACAO_ID, $situacoesPermitidas, true),
            422,
            'A situação do chamado foi alterada.'
        );
    }

    private function registrarCancelamento(Chamado $chamado, $motivoId, $motivacao)
    {
        $motivacao = trim((string) $motivacao);
        abort_if($motivacao === '', 422, 'A motivação do cancelamento é obrigatória.');

        $motivo = DB::table('TABELA_GENERICA')
            ->where('TABELA_ID', RTG::MOTIVO_CANCELAMENTO)
            ->where('COLUNA_ID', $motivoId)
            ->value('DESCRICAO');

        abort_unless($motivo, 422, 'Motivo de cancelamento inválido.');

        Cancelamento::create([
            'CHAMADO_ID' => $chamado->CHAMADO_ID,
            'TG_CHAMADO_ID' => $motivoId,
        ]);

        $this->registrarSituacao(
            $chamado,
            SituacaoChamadoEnum::CANCELADO,
            'Motivo: ' . $motivo . ' - Motivação: ' . $motivacao
        );
    }

    private function registrarSituacao(Chamado $chamado, $situacao, $observacao = null)
    {
        ChamadoSituacao::create([
            'CHAMADO_ID' => $chamado->CHAMADO_ID,
            'TG_SITUACAO_ID' => $situacao,
            'CHAMADO_SITUACAO_DATA' => Carbon::now('America/Sao_Paulo'),
            'CHAMADO_SITUACAO_OBSERVACAO' => $observacao,
            'USUARIO_ID' => Auth::id(),
        ]);
    }

    private function bloquearRecursosAtivos(Chamado $chamado)
    {
        $vinculos = ChamadoEquipe::where('CHAMADO_ID', $chamado->CHAMADO_ID)
            ->where('CHAMADO_EQUIPE_ATIVO', 1)
            ->lockForUpdate()
            ->get();

        abort_if($vinculos->isEmpty(), 422, 'O chamado não possui equipe e veículo vinculados.');

        foreach ($vinculos as $vinculo) {
            $equipe = Equipe::lockForUpdate()->findOrFail($vinculo->EQUIPE_ID);
            Veiculo::lockForUpdate()->findOrFail($equipe->VEICULO_ID);
        }

        return $vinculos;
    }

    private function liberarRecursos($vinculos)
    {
        foreach ($vinculos as $vinculo) {
            $vinculo->CHAMADO_EQUIPE_ATIVO = 0;
            $vinculo->save();

            $equipe = Equipe::lockForUpdate()->find($vinculo->EQUIPE_ID);
            if ($equipe) {
                Veiculo::where('VEICULO_ID', $equipe->VEICULO_ID)
                    ->lockForUpdate()
                    ->update(['TG_SITUACAO_VEICULO_ID' => 1]);
            }
        }
    }
}
