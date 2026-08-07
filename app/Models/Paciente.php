<?php

namespace App\Models;

use App\Casts\Cpf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $table = "PACIENTE";
    protected $primaryKey = "PACIENTE_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        "PACIENTE_NOME",
        "PACIENTE_CPF",
        "PACIENTE_DT_NASCIMENTO",
        "TG_SEXO_ID",
        "USUARIO_ID",
        "PACIENTE_DT_CAD",
        "PACIENTE_DT_IDENTIFICACAO",
    ];

    protected $casts = [
        "PACIENTE_ID" => "integer",
        "PACIENTE_CPF" => Cpf::class,
        "PACIENTE_DT_NASCIMENTO" => "date:Y-m-d",
        "TG_SEXO_ID" => "integer",
        "USUARIO_ID" => "integer",
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, "USUARIO_ID", "USUARIO_ID");
    }

    public static function pesquisar($request)
    {
        return self::query()
            ->when($request->PACIENTE_ID, function (Builder $query) use ($request) {
                return $query->where("PACIENTE_ID", $request->PACIENTE_ID);
            })
            ->when($request->PACIENTE_NOME, function (Builder $query) use ($request) {
                return $query->where("PACIENTE_NOME", "like", "%{$request->PACIENTE_NOME}%");
            })
            ->when($request->PACIENTE_CPF, function (Builder $query) use ($request) {
                $cpf = preg_replace("/\D/", "", $request->PACIENTE_CPF);
                return $query->where("PACIENTE_CPF", $cpf);
            })
            ->when($request->TG_SEXO_ID, function (Builder $query) use ($request) {
                return $query->where("TG_SEXO_ID", $request->TG_SEXO_ID);
            })
            ->orderBy("PACIENTE_NOME")
            ->orderByDesc("PACIENTE_ID");
    }

    public static function buscar($id)
    {
        return self::with("usuario")->findOrFail($id);
    }
}
