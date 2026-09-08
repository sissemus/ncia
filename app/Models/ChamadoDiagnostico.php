<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChamadoDiagnostico extends Model
{
    protected $table = "CHAMADO_DIAGNOSTICO";
    protected $primaryKey = "CHAMADO_DIAGNOSTICO_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        "CHAMADO_ID",
        "DIAGNOSTICO_ID",
    ];

    protected $casts = [
        "CHAMADO_DIAGNOSTICO_ID" => "integer",
        "CHAMADO_ID" => "integer",
        "DIAGNOSTICO_ID" => "integer",
    ];

    public function chamado()
    {
        return $this->belongsTo(Chamado::class, "CHAMADO_ID", "CHAMADO_ID");
    }

    public function diagnostico()
    {
        return $this->belongsTo(Diagnostico::class, "DIAGNOSTICO_ID", "DIAGNOSTICO_ID");
    }
}
