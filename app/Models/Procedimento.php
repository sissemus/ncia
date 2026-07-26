<?php

namespace App\Models;

use App\MyLibs\RTG;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Procedimento extends Model
{
    protected $table = "PROCEDIMENTO";
    protected $primaryKey = "PROCEDIMENTO_ID";
    public $timestamps = false;

    protected $fillable = [
        "PROCEDIMENTO_CODIGO",
        "PROCEDIMENTO_DESCRICAO",
        "PROCEDIMENTO_ATIVO",
    ];

    protected $casts = [
        "PROCEDIMENTO_ID" => "integer",
        "PROCEDIMENTO_CODIGO" => "string",
        "PROCEDIMENTO_DESCRICAO" => "string",
        "PROCEDIMENTO_ATIVO" => "integer",
    ];

    public static function pesquisar($requisicao)
    {
        return self::when($requisicao->PROCEDIMENTO_DESCRICAO, function (Builder $query) use ($requisicao) {
                return $query->where("PROCEDIMENTO_DESCRICAO", "like", "%" . $requisicao->PROCEDIMENTO_DESCRICAO . "%");
            })
            ->orderBy('PROCEDIMENTO_DESCRICAO')
            ->paginate();
    }

    public static function buscar($id)
    {
        return self::find($id);
    }
}
