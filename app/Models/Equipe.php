<?php

namespace App\Models;

use App\MyLibs\RTG;
use App\MyLibs\SituacaoChamadoEnum;
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

    private static function filtrarVinculoPendente($query)
    {
        $query->where('CHAMADO_EQUIPE.CHAMADO_EQUIPE_ATIVO', 1)
            ->whereRaw(
                '(SELECT TOP 1 CS.TG_SITUACAO_ID FROM CHAMADO_SITUACAO CS'
                . ' WHERE CS.CHAMADO_ID = CHAMADO_EQUIPE.CHAMADO_ID'
                . ' ORDER BY CS.CHAMADO_SITUACAO_DATA DESC, CS.CHAMADO_SITUACAO_ID DESC) IN (?, ?)',
                [SituacaoChamadoEnum::EM_ATENDIMENTO, SituacaoChamadoEnum::EM_FILA]
            );
    }

    public static function pesquisar($requisicao)
    {
        return self::with([
                'veiculo',
                'equipeProfissional',
                'equipeProfissional.profissional',
                'equipeProfissional.profissional.tipoProfissional',
                'chamadoEquipe'
            ])
            ->when($requisicao->EQUIPE_ID, function (Builder $query) use ($requisicao) {
                return $query->where("EQUIPE_ID", "=", $requisicao->EQUIPE_ID);
            })
            ->when($requisicao->VEICULO_ID, function (Builder $query) use ($requisicao) {
                return $query->where("VEICULO_ID", "=", $requisicao->VEICULO_ID);
            })
            ->orderBy('VEICULO_ID')
            ->paginate();
    }

    public static function buscar($id)
    {
        return self::find($id);
    }
}