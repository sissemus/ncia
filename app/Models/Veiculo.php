<?php

namespace App\Models;

use App\MyLibs\RTG;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Veiculo extends Model
{
    protected $table = "VEICULO";
    protected $primaryKey = "VEICULO_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        "VEICULO_IDENTIFICACAO",
        "VEICULO_PLACA",
        "TG_TIPO_VEICULO_ID",
        "TG_SITUACAO_VEICULO_ID",
        "VEICULO_ATIVO",
    ];

    protected $casts = [
        "VEICULO_ID" => "integer",
        "VEICULO_IDENTIFICACAO" => "string",
        "VEICULO_PLACA" => "string",
        "TG_TIPO_VEICULO_ID" => "integer",
        "TG_SITUACAO_VEICULO_ID" => "integer",
        "VEICULO_ATIVO" => "integer",
    ];

    public function tipoVeiculo()
    {
        return $this->belongsTo(TabelaGenerica::class, "TG_TIPO_VEICULO_ID", "COLUNA_ID")
            ->where('TABELA_ID', RTG::TIPO_VEICULO);
    }

    public function situacaoVeiculo()
    {
        return $this->belongsTo(TabelaGenerica::class, "TG_SITUACAO_VEICULO_ID", "COLUNA_ID")
            ->where('TABELA_ID', RTG::SITUACAO_VEICULO);
    }

    public static function relacionamento()
    {
        return [
            "tipoVeiculo",
            "situacaoVeiculo",
        ];
    }

    public static function pesquisar($requisicao)
    {
        return self::with(self::relacionamento())
            ->when($requisicao->VEICULO_IDENTIFICACAO, function (Builder $query) use ($requisicao) {
                return $query->where("VEICULO_IDENTIFICACAO", "like", "%" . $requisicao->VEICULO_IDENTIFICACAO . "%");
            })
            ->when($requisicao->TG_TIPO_VEICULO_ID, function (Builder $query) use ($requisicao) {
                return $query->where("TG_TIPO_VEICULO_ID", $requisicao->TG_TIPO_VEICULO_ID);
            })
            ->when($requisicao->TG_SITUACAO_VEICULO_ID, function (Builder $query) use ($requisicao) {
                return $query->where("TG_SITUACAO_VEICULO_ID", $requisicao->TG_SITUACAO_VEICULO_ID);
            })
            ->when($requisicao->VEICULO_ATIVO !== null && $requisicao->VEICULO_ATIVO !== '', function (Builder $query) use ($requisicao) {
                return $query->where("VEICULO_ATIVO", $requisicao->VEICULO_ATIVO);
            })
            ->orderBy("VEICULO_IDENTIFICACAO")
            ->paginate();
    }

    public static function buscar($id)
    {
        return self::with(self::relacionamento())->findOrFail($id);
    }
}
