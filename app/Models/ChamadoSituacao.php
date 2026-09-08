<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChamadoSituacao extends Model
{
    protected $table = "CHAMADO_SITUACAO";
    protected $primaryKey = "CHAMADO_SITUACAO_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        "TG_SITUACAO_ID",
        "CHAMADO_ID",
        "CHAMADO_SITUACAO_DATA",
        "CHAMADO_SITUACAO_OBSERVACAO",
        "USUARIO_ID",
    ];

    protected $casts = [
        "CHAMADO_SITUACAO_ID" => "integer",
        "TG_SITUACAO_ID" => "integer",
        "CHAMADO_ID" => "integer",
        "CHAMADO_SITUACAO_DATA" => "datetime",
        "USUARIO_ID" => "integer",
    ];

    public function chamado()
    {
        return $this->belongsTo(Chamado::class, "CHAMADO_ID", "CHAMADO_ID");
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, "USUARIO_ID", "USUARIO_ID");
    }
}
