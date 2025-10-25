<?php

namespace App\Models;

use App\MyLibs\RTG;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $table = "DEPARTAMENTO";
    protected $primaryKey = "DEPARTAMENTO_ID";
    public $timestamps = false;

    protected $fillable = [
        "HIERARQUIA_ID",
        "DEPARTAMENTO_NOME",
        "DEPARTAMENTO_SIGLA",
        "DEPARTAMENTO_DESCRICAO",
        "DEPARTAMENTO_ATIVO",
    ];

    protected $casts = [
        "DEPARTAMENTO_ID" => "integer",
        "HIERARQUIA_ID" => "integer",
        "DEPARTAMENTO_NOME" => "string",
        "DEPARTAMENTO_SIGLA" => "string",
        "DEPARTAMENTO_DESCRICAO" => "string",
        "DEPARTAMENTO_ATIVO" => "integer",
    ];

    public static $relacionamento = [
        "hierarquia",
    ];


    public function hierarquia()
    {
        return $this->hasOne(TabelaGenerica::class, "COLUNA_ID", "HIERARQUIA_ID")
            ->where('TABELA_ID', RTG::HIERARQUIA)
            ->where("COLUNA_ID", "!=", 0);
    }

    public static function pesquisar($requisicao)
    {
        return self::with(self::$relacionamento)
            ->when($requisicao->DEPARTAMENTO_NOME, function (Builder $query) use ($requisicao) {
                return $query->where("DEPARTAMENTO_NOME", "like", "%" . $requisicao->DEPARTAMENTO_NOME . "%");
            })
            ->when($requisicao->DEPARTAMENTO_SIGLA, function (Builder $query) use ($requisicao) {
                return $query->where("DEPARTAMENTO_SIGLA", "like", "%" . $requisicao->DEPARTAMENTO_SIGLA . "%");
            })
            ->where('DEPARTAMENTO_EXCLUSAO', null)
            ->paginate();
    }

    public static function buscar($id)
    {
        return self::with(self::$relacionamento)->find($id);
    }
}
