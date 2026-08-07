<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class VeiculoUnidade extends Model
{
    protected $table = "VEICULO_UNIDADE";
    protected $primaryKey = "VEICULO_UNIDADE_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        "VEICULO_ID",
        "UNIDADE_ID",
        "VEICULO_UNIDADE_DT_INI",
        "VEICULO_UNIDADE_DT_FIM",
    ];

    protected $casts = [
        "VEICULO_UNIDADE_ID" => "integer",
        "VEICULO_ID" => "integer",
        "UNIDADE_ID" => "integer",
        "VEICULO_UNIDADE_DT_INI" => "datetime",
        "VEICULO_UNIDADE_DT_FIM" => "datetime",
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, "VEICULO_ID", "VEICULO_ID");
    }

    public function unidade()
    {
        return $this->belongsTo(Unidade::class, "UNIDADE_ID", "UNIDADE_ID");
    }

    public static function relacionamento()
    {
        return [
            "veiculo",
            "unidade",
        ];
    }

    public static function pesquisar($requisicao)
    {
        return self::with(self::relacionamento())
            ->when($requisicao->VEICULO_ID, function (Builder $query) use ($requisicao) {
                return $query->where("VEICULO_ID", $requisicao->VEICULO_ID);
            })
            ->when($requisicao->UNIDADE_ID, function (Builder $query) use ($requisicao) {
                return $query->where("UNIDADE_ID", $requisicao->UNIDADE_ID);
            })
            ->when($requisicao->STATUS === 'ativo', function (Builder $query) {
                return $query->whereNull("VEICULO_UNIDADE_DT_FIM");
            })
            ->when($requisicao->STATUS === 'historico', function (Builder $query) {
                return $query->whereNotNull("VEICULO_UNIDADE_DT_FIM");
            })
            ->orderByDesc("VEICULO_UNIDADE_DT_INI")
            ->paginate();
    }

    /**
     * Víncula um veículo a uma unidade, fechando qualquer vínculo ativo anterior deste veículo.
     */
    public static function vincular($veiculoId, $unidadeId)
    {
        // 1. Fechar qualquer vínculo ativo anterior para este veículo
        self::ondeAtivo($veiculoId)->update([
            'VEICULO_UNIDADE_DT_FIM' => Carbon::now()
        ]);

        // 2. Criar novo vínculo
        return self::create([
            'VEICULO_ID' => $veiculoId,
            'UNIDADE_ID' => $unidadeId,
            'VEICULO_UNIDADE_DT_INI' => Carbon::now(),
            'VEICULO_UNIDADE_DT_FIM' => null
        ]);
    }

    /**
     * Desvíncula um veículo fechando o vínculo ativo.
     */
    public static function desvincular($veiculoId)
    {
        return self::ondeAtivo($veiculoId)->update([
            'VEICULO_UNIDADE_DT_FIM' => Carbon::now()
        ]);
    }

    /**
     * Helper para buscar o vínculo ativo de um veículo.
     */
    public static function ondeAtivo($veiculoId)
    {
        return self::where('VEICULO_ID', $veiculoId)->whereNull('VEICULO_UNIDADE_DT_FIM');
    }
}
