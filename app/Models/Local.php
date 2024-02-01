<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer LOCAL_ID
 * @property string LOCAL_DESCRICAO
 * @property string LOCAL_ENDERECO
 * @property string LOCAL_TELEFONE
 * @property integer LOCAL_TIPO
 * @property string LOCAL_ATUALIZACAO
 * @property integer LOCAL_ATIVO
 * @method static Local find(mixed $LOCAL_ID)
 */
class Local extends Model {
    use HasFactory;

    protected $table = "LOCAL";
    protected $primaryKey = "LOCAL_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;
    protected $fillable = [
        "LOCAL_DESCRICAO",
        "LOCAL_ENDERECO",
        "LOCAL_TELEFONE",
        "LOCAL_ABERTURA",
        "LOCAL_FECHAMENTO",
        "LOCAL_TIPO",
        "LOCAL_ATUALIZACAO",
        "LOCAL_ATIVO",
    ];
    protected $casts = [
        "LOCAL_ID" => "integer",
        "LOCAL_TIPO" => "integer",
        "LOCAL_ATIVO" => "integer",
    ];
    public static $relacionamentos = [
        "localSituacoes.situacao",
        "localSituacaoUltima.situacao",
        "publicos",
        "publicoUltimo",
    ];

    public function localSituacaoUltima() {
        return $this->hasOne(LocalSituacao::class, "LOCAL_ID", "LOCAL_ID")
            ->where("LOCAL_SITUACAO_ULTIMO", 1);
    }

    public function localSituacoes() {
        return $this->hasMany(LocalSituacao::class, "LOCAL_ID", "LOCAL_ID");
    }

    public function publicoUltimo() {
        return $this->hasOne(Publico::class, "LOCAL_ID", "LOCAL_ID")
            ->where("PUBLICO_ULTIMO", 1);
    }

    public function publicos() {
        return $this->hasMany(Publico::class, "LOCAL_ID", "LOCAL_ID");
    }

    public function vacinaLocais() {
        return $this->hasMany(VacinaLocal::class, "LOCAL_ID", "LOCAL_ID");
    }

    public static function listByLocalTipo($localTipo) {
        return self::with(self::$relacionamentos)
            ->where("LOCAL_TIPO", $localTipo)
            ->where("LOCAL_ATIVO", 1)
            ->get();
    }

    public static function listar() {
        return self::with(self::$relacionamentos)->get();
    }


    public static function getByLocalId($localId) {
        return self::with(self::$relacionamentos)->find($localId);
    }
}
