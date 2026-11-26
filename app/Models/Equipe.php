<?php

namespace App\Models;

use App\MyLibs\RTG;
use App\MyLibs\SituacaoChamadoEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Equipe extends Model
{
    protected $table = "EQUIPE";
    protected $primaryKey = "EQUIPE_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        "VEICULO_ID",
        "EQUIPE_DATA",
        "EQUIPE_DATA_FIM",
        "EQUIPE_TURNO",
        "EQUIPE_ATIVO",
        "USUARIO_ID_CAD",
    ];

    protected $casts = [
        "EQUIPE_ID" => "integer",
        "VEICULO_ID" => "integer",
        "EQUIPE_DATA" => "datetime:Y-m-d H:i:s",
        "EQUIPE_DATA_FIM" => "datetime:Y-m-d H:i:s",
        "EQUIPE_TURNO" => "string",
        "EQUIPE_ATIVO" => "integer",
        "USUARIO_ID_CAD" => "integer",
        "CHAMADOS_PENDENTES" => "integer",
    ];

    public function veiculo(){
        return $this->hasOne(Veiculo::class, 'VEICULO_ID', 'VEICULO_ID');
    }

    public function equipeProfissional(){
        return $this->hasMany(EquipeProfissional::class, 'EQUIPE_ID', 'EQUIPE_ID');
    }

    public function chamadoEquipe(){
        return $this->hasMany(ChamadoEquipe::class, 'EQUIPE_ID', 'EQUIPE_ID');
    }

    /**
     * Vínculos ativos com chamados cuja situação ATUAL (última por data/ID) é
     * EM ATENDIMENTO ou EM FILA. Equipe nessa condição não pode ser desativada
     * (nem pela rotina, nem pelos botões, nem pela lixeira) até trocar o
     * plantão ou concluir/cancelar o chamado.
     */
    public function chamadosPendentes()
    {
        return $this->chamadoEquipe()->where(function ($q) {
            self::filtrarVinculoPendente($q);
        });
    }

    public function scopeComChamadoPendente(Builder $query)
    {
        return $query->whereHas('chamadoEquipe', function ($q) {
            self::filtrarVinculoPendente($q);
        });
    }

    public function scopeSemChamadoPendente(Builder $query)
    {
        return $query->whereDoesntHave('chamadoEquipe', function ($q) {
            self::filtrarVinculoPendente($q);
        });
    }

    /**
     * Equipes com ao menos um integrante ativo cujo nome contenha o texto.
     */
    public function scopeComProfissionalNome(Builder $query, $nome)
    {
        return $query->whereHas('equipeProfissional', function ($ep) use ($nome) {
            $ep->where('EQUIPE_PROFISSIONAL_ATIVO', 1)
                ->whereHas('profissional', function ($p) use ($nome) {
                    $p->where('PROFISSIONAL_NOME', 'like', '%' . $nome . '%');
                });
        });
    }

    public static function filtrarVinculoPendente($query)
    {
        $query->where('CHAMADO_EQUIPE.CHAMADO_EQUIPE_ATIVO', 1)
            ->whereRaw(
                '(SELECT TOP 1 CS.TG_SITUACAO_ID FROM CHAMADO_SITUACAO CS'
                . ' WHERE CS.CHAMADO_ID = CHAMADO_EQUIPE.CHAMADO_ID'
                . ' ORDER BY CS.CHAMADO_SITUACAO_DATA DESC, CS.CHAMADO_SITUACAO_ID DESC) IN (?, ?)',
                [SituacaoChamadoEnum::EM_ATENDIMENTO, SituacaoChamadoEnum::EM_FILA]
            );
    }

    /**
     * Lista paginada do Cadastro de Equipes (uma linha por equipe, com seus
     * profissionais). Filtros: SITUACAO (ATIVAS padrão / DESATIVADAS / TODAS),
     * EQUIPE_ID, VEICULO_ID, VEICULO_IDENTIFICACAO, EQUIPE_TURNO,
     * PROFISSIONAL_NOME e SITUACAO_PLANTAO (VIGENTE / ENCERRADO).
     */
    public static function pesquisar($requisicao)
    {
        $agora = Carbon::now()->format('Y-m-d H:i:s');
        $situacao = in_array($requisicao->SITUACAO, ['ATIVAS', 'DESATIVADAS', 'TODAS'], true)
            ? $requisicao->SITUACAO
            : 'ATIVAS';
        $turno = in_array($requisicao->EQUIPE_TURNO, ['SD', 'SN'], true) ? $requisicao->EQUIPE_TURNO : null;
        $veiculo = trim((string) $requisicao->VEICULO_IDENTIFICACAO);
        $profissional = trim((string) $requisicao->PROFISSIONAL_NOME);

        return self::with([
                'veiculo',
                'equipeProfissional' => function ($q) {
                    $q->where('EQUIPE_PROFISSIONAL_ATIVO', 1);
                },
                'equipeProfissional.profissional',
                'equipeProfissional.profissional.tipoProfissional',
            ])
            ->withCount(['chamadoEquipe as CHAMADOS_PENDENTES' => function ($q) {
                self::filtrarVinculoPendente($q);
            }])
            ->when($situacao === 'ATIVAS', function (Builder $query) {
                $query->where('EQUIPE_ATIVO', 1);
            })
            ->when($situacao === 'DESATIVADAS', function (Builder $query) {
                $query->where('EQUIPE_ATIVO', 0);
            })
            ->when($requisicao->EQUIPE_ID, function (Builder $query) use ($requisicao) {
                $query->where('EQUIPE_ID', (int) $requisicao->EQUIPE_ID);
            })
            ->when($requisicao->VEICULO_ID, function (Builder $query) use ($requisicao) {
                $query->where('VEICULO_ID', (int) $requisicao->VEICULO_ID);
            })
            ->when($veiculo !== '', function (Builder $query) use ($veiculo) {
                $query->whereHas('veiculo', function ($v) use ($veiculo) {
                    $v->where('VEICULO_IDENTIFICACAO', 'like', '%' . $veiculo . '%');
                });
            })
            ->when($turno, function (Builder $query) use ($turno) {
                $query->where('EQUIPE_TURNO', $turno);
            })
            ->when($profissional !== '', function (Builder $query) use ($profissional) {
                $query->comProfissionalNome($profissional);
            })
            ->when($requisicao->SITUACAO_PLANTAO === 'VIGENTE', function (Builder $query) use ($agora) {
                $query->where('EQUIPE_DATA_FIM', '>=', $agora);
            })
            ->when($requisicao->SITUACAO_PLANTAO === 'ENCERRADO', function (Builder $query) use ($agora) {
                $query->where('EQUIPE_DATA_FIM', '<', $agora);
            })
            ->orderByDesc('EQUIPE_ATIVO')
            ->orderByDesc('EQUIPE_DATA')
            ->orderByDesc('EQUIPE_ID')
            ->paginate(15);
    }

    public static function buscar($id)
    {
        return self::find($id);
    }
}