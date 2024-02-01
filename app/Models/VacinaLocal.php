<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string VACINA_LOCAL_ID
 * @property string VACINA_LOCAL_DH_CADASTRO
 * @property integer VACINA_ID
 * @property integer LOCAL_ID
 * @property integer VACINA_LOCAL_QTD
 * @method static VacinaLocal find(mixed $input)
 */
class VacinaLocal extends Model {
    use HasFactory;

    protected $table = "VACINA_LOCAL";
    protected $primaryKey = "VACINA_LOCAL_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;
    protected $fillable = [
        "VACINA_LOCAL_DH_CADASTRO",
        "VACINA_ID",
        "LOCAL_ID",
        "VACINA_LOCAL_QTD",
    ];
    protected $casts = [
        "VACINA_ID" => "integer",
        "LOCAL_ID" => "integer",
        "VACINA_LOCAL_QTD" => "integer",
    ];
    public static $rels1 = [
        "local",
        "vacina"
    ];

    public function local() {
        return $this->hasOne(Local::class, "LOCAL_ID", "LOCAL_ID");
    }

    public function vacina() {
        return $this->hasOne(Vacina::class, "VACINA_ID", "VACINA_ID");
    }

    public function vacinacoes() {
        return $this->hasMany(Vacinacao::class, "VACINA_LOCAL_ID", "VACINA_LOCAL_ID");
    }

    public static function search() {
        return self::with(["local", "vacina"])->paginate();
    }

    public static function getByLocalId($localId) {
        return self::with(["vacina"])
            ->where("LOCAL_ID", $localId)->get();
    }

    public static function getByVacinaId($vacinaId) {
        return self::with(["vacina"])
            ->where("VACINA_ID", $vacinaId)
            ->get();
    }

    public static function getByUsuarioId($localId) {
        return self::with([
            "vacina",
            "local"
        ])
            ->where("LOCAL_ID", $localId)->get();
    }
}
