<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioUnidade extends Model
{
    protected $table = "USUARIO_UNIDADE";
    protected $primaryKey = "USUARIO_UNIDADE_ID";
    public $timestamps = false;

    protected $fillable = [
        "USUARIO_ID",
        "UNIDADE_ID",
    ];

    protected $casts = [
        "USUARIO_UNIDADE_ID" => "integer",
        "USUARIO_ID" => "integer",
        "UNIDADE_ID" => "integer",
    ];

    public function unidade()
    {
        return $this->belongsTo(Unidade::class, "UNIDADE_ID", "UNIDADE_ID");
    }

    public static function listar($request)
    {
        return self::with("unidade")
            ->where("USUARIO_ID", $request->USUARIO_ID)
            ->get();
    }

    public static function buscar($id)
    {
        return self::findOrFail($id);
    }
}
