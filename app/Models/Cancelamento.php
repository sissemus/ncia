<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cancelamento extends Model
{
    protected $table = "CANCELAMENTO";
    protected $primaryKey = "CANCELAMENTO_ID";
    public $timestamps = false;

    protected $fillable = [
        "CHAMADO_ID",
        "TG_CHAMADO_ID",
    ];

    protected $casts = [
        "CANCELAMENTO_ID" => "integer",
        "CHAMADO_ID" => "integer",
        "TG_CHAMADO_ID" => "integer",
    ];
}
