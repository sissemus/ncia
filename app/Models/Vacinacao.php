<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer VACINACAO_ID
 * @property string VACINACAO_DH
 * @property integer VACINA_LOCAL_ID
 * @property integer VACINACAO_QTD
 * @property integer DOSE_ID
 */
class Vacinacao extends Model {
    use HasFactory;

    protected $table = "VACINACAO";
    protected $primaryKey = "VACINACAO_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;
    protected $fillable = [
        "VACINACAO_DH",
        "VACINA_LOCAL_ID",
        "VACINACAO_QTD",
        "DOSE_ID",
    ];
    protected $casts = [
        "VACINACAO_QTD" => "integer"
    ];

    public static $rels1 = [
        "vacinaLocal.vacina",
        "vacinaLocal.local",
        "dose"
    ];

    public function vacinaLocal() {
        return $this->hasOne(VacinaLocal::class, "VACINA_LOCAL_ID", "VACINA_LOCAL_ID");
    }

    public function dose() {
        return $this->hasOne(Dose::class, "DOSE_ID", "DOSE_ID");
    }

    public static function getByVacinaLocalId($vacinaLocalId, $rels = null) {
        return self::with($rels == null ? self::$rels1 : $rels)
            ->where("VACINA_LOCAL_ID", $vacinaLocalId)
            ->paginate();
    }
}
