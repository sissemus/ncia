<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Unidade extends Model
{
    protected $table = "UNIDADE";
    protected $primaryKey = "UNIDADE_ID";
    public $timestamps = false;

    protected $fillable = [
        "UNIDADE_NOME",
        "UNIDADE_SOLICITANTE",
        "UNIDADE_ATIVO",
    ];

    protected $casts = [
        "UNIDADE_ID" => "integer",
        "UNIDADE_NOME" => "string",
        "UNIDADE_SOLICITANTE" => "integer",
        "UNIDADE_ATIVO" => "integer",
    ];

    public static function pesquisar($requisicao)
    {
        return self::query()
            ->when($requisicao->UNIDADE_NOME, function (Builder $query) use ($requisicao) {
                return $query->where("UNIDADE_NOME", "like", "%" . $requisicao->UNIDADE_NOME . "%");
            })
            ->when(
                $requisicao->UNIDADE_SOLICITANTE !== null && $requisicao->UNIDADE_SOLICITANTE !== '',
                function (Builder $query) use ($requisicao) {
                    return $query->where("UNIDADE_SOLICITANTE", $requisicao->UNIDADE_SOLICITANTE);
                }
            )
            ->when(
                $requisicao->UNIDADE_ATIVO !== null && $requisicao->UNIDADE_ATIVO !== '',
                function (Builder $query) use ($requisicao) {
                    return $query->where("UNIDADE_ATIVO", $requisicao->UNIDADE_ATIVO);
                }
            )
            ->orderBy("UNIDADE_NOME")
            ->paginate();
    }

    public static function buscar($id)
    {
        return self::find($id);
    }
}
