<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer SITUACAO_ID
 * @property string SITUACAO_NOME
 * @property integer SITUACAO_ESCALA
 * @property integer SITUACAO_ATIVA
 */
class Situacao extends Model {
    use HasFactory;

    protected $table = "SITUACAO";
    protected $primaryKey = "SITUACAO_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;
    protected $fillable = [
        "SITUACAO_NOME",
        "SITUACAO_ESCALA",
        "SITUACAO_ATIVA",
    ];
    protected $casts = [
        "SITUACAO_ID" => "integer",
        "SITUACAO_ESCALA" => "integer",
        "SITUACAO_ATIVA" => "integer",
    ];
}
