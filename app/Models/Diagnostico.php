<?php

namespace App\Models;

use App\MyLibs\RTG;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Diagnostico extends Model
{
    protected $table = "DIAGNOSTICO";
    protected $primaryKey = "DIAGNOSTICO_ID";
    public $timestamps = false;

    protected $fillable = [
        "DIAGNOSTICO_DESCRICAO",
        "DIAGNOSTICO_ATIVO",
    ];

    protected $casts = [
        "DIAGNOSTICO_ID" => "integer",
        "DIAGNOSTICO_DESCRICAO" => "string",
        "DIAGNOSTICO_ATIVO" => "integer",
    ];

    public static function pesquisar($requisicao)
    {
        return self::when($requisicao->DIAGNOSTICO_DESCRICAO, function (Builder $query) use ($requisicao) {
                return $query->where("DIAGNOSTICO_DESCRICAO", "like", "%" . $requisicao->DIAGNOSTICO_DESCRICAO . "%");
            })
            ->orderBy('DIAGNOSTICO_DESCRICAO')
            ->paginate();
    }

    public static function buscar($id)
    {
        return self::find($id);
    }
}
