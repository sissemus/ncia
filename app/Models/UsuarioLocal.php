<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer USUARIO_LOCAL_ID
 * @property integer USUARIO_ID
 * @property integer LOCAL_ID
 * @property integer USUARIO_LOCAL_ATIVO
 */
class UsuarioLocal extends Model {
    use HasFactory;

    protected $table = "USUARIO_LOCAL";
    protected $primaryKey = "USUARIO_LOCAL_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;
    protected $fillable = [
        "USUARIO_ID",
        "LOCAL_ID",
        "USUARIO_LOCAL_ATIVO",
    ];
    protected $casts = [
        "USUARIO_ID" => "integer",
        "LOCAL_ID" => "integer",
        "USUARIO_LOCAL_ATIVO" => "integer",
    ];
    public static $relacionamentos = [
        "local.publicoUltimo",
        "local.vacinaLocais.vacina"
    ];

    public static $rels2 = [
//        "local.publicoUltimo",
        "local.vacinaLocais.vacina"
    ];

    public function local() {
        return $this->hasOne(Local::class, "LOCAL_ID", "LOCAL_ID");
    }

    public static function getByUsuarioId($usuarioId, $rels = null) {
        return self::with($rels == null ? self::$relacionamentos : $rels)
            ->where("USUARIO_ID", $usuarioId)
            ->get();
    }

    public static function deleteByUsuarioId($usuarioId) {
        self::with([])->where("USUARIO_ID", $usuarioId)->delete();
    }
}
