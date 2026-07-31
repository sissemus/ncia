<?php

namespace App\Models;

use App\Casts\Cpf;
use App\MyLibs\RTG;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Profissional extends Model
{
    protected $table = "PROFISSIONAL";
    protected $primaryKey = "PROFISSIONAL_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        "PROFISSIONAL_NOME",
        "PROFISSIONAL_CPF",
        "PROFISSIONAL_NASCIMENTO",
        "TG_SEXO_ID",
        "TG_TIPO_PROFISSIONAL_ID",
        "PROFISSIONAL_ATIVO",
    ];

    protected $casts = [
        "PROFISSIONAL_ID" => "integer",
        "PROFISSIONAL_NOME" => "string",
        "PROFISSIONAL_CPF" => Cpf::class,
        "PROFISSIONAL_NASCIMENTO" => "date",
        "TG_SEXO_ID" => "integer",
        "TG_TIPO_PROFISSIONAL_ID" => "integer",
        "PROFISSIONAL_ATIVO" => "integer",
    ];

    public function sexo()
    {
        return $this->belongsTo(TabelaGenerica::class, "TG_SEXO_ID", "COLUNA_ID")
            ->where('TABELA_ID', RTG::SEXO);
    }

    public function tipoProfissional()
    {
        return $this->belongsTo(TabelaGenerica::class, "TG_TIPO_PROFISSIONAL_ID", "COLUNA_ID")
            ->where('TABELA_ID', RTG::TIPO_PROFISSIONAL);
    }

    public static function relacionamento()
    {
        return [
            "sexo",
            "tipoProfissional",
        ];
    }

    public static function listar($request)
    {
        return self::with(self::relacionamento())
            ->when($request->PROFISSIONAL_NOME, function (Builder $query) use ($request) {
                return $query->where(
                    "PROFISSIONAL_NOME",
                    "like",
                    "%{$request->PROFISSIONAL_NOME}%"
                );
            })
            ->when($request->PROFISSIONAL_CPF, function (Builder $query) use ($request) {
                return $query->where(
                    "PROFISSIONAL_CPF",
                    "like",
                    "%{$request->PROFISSIONAL_CPF}%"
                );
            })
            ->when($request->TG_SEXO_ID, function (Builder $query) use ($request) {
                return $query->where("TG_SEXO_ID", $request->TG_SEXO_ID);
            })
            ->when($request->TG_TIPO_PROFISSIONAL_ID, function (Builder $query) use ($request) {
                return $query->where(
                    "TG_TIPO_PROFISSIONAL_ID",
                    $request->TG_TIPO_PROFISSIONAL_ID
                );
            })
            ->when($request->PROFISSIONAL_ATIVO !== null, function (Builder $query) use ($request) {
                return $query->where("PROFISSIONAL_ATIVO", $request->PROFISSIONAL_ATIVO);
            })
            ->orderBy("PROFISSIONAL_NOME");
    }

    public static function buscar($id)
    {
        return self::with(self::relacionamento())
            ->findOrFail($id);
    }
}
