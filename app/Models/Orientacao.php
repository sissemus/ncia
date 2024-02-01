<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer ORIENTACAO_ID
 * @property string ORIENTACAO_MENSAGEM
 * @property integer ORIENTACAO_ATIVA
 */
class Orientacao extends Model {
    use HasFactory;
    use HasFactory;

    protected $table = "ORIENTACAO";
    protected $primaryKey = "ORIENTACAO_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;
    protected $fillable = [

    ];
    protected $casts = [
        "ORIENTACAO_ID" => "integer",
        "ORIENTACAO_ATIVA" => "integer",
    ];

    public static function listAll($soAtivos = 1) {
        return self::with([])
            ->when($soAtivos, function ($q) {
                $q->where("ORIENTACAO_ATIVA", 1);
            })
            ->get();
    }
}
