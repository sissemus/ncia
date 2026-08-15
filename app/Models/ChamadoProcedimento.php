<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChamadoProcedimento extends Model
{
    protected $table = "CHAMADO_PROCEDIMENTO";
    protected $primaryKey = "CHAMADO_PROCEDIMENTO_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        "CHAMADO_ID",
        "PROCEDIMENTO_ID",
    ];

    protected $casts = [
        "CHAMADO_PROCEDIMENTO_ID" => "integer",
        "CHAMADO_ID" => "integer",
        "PROCEDIMENTO_ID" => "integer",
    ];

    public function chamado()
    {
        return $this->belongsTo(Chamado::class, "CHAMADO_ID", "CHAMADO_ID");
    }

    public function procedimento()
    {
        return $this->belongsTo(Procedimento::class, "PROCEDIMENTO_ID", "PROCEDIMENTO_ID");
    }
}
