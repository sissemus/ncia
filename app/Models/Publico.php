<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer PUBLICO_ID
 * @property string PUBLICO_DESCRICAO
 * @property string PUBLICO_DATA
 * @property integer PUBLICO_ULTIMO
 * @property integer LOCAL_ID
 */
class Publico extends Model {
    use HasFactory;

    protected $table = "PUBLICO";
    protected $primaryKey = "PUBLICO_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;
    protected $fillable = [
        "PUBLICO_DESCRICAO",
        "PUBLICO_DATA",
        "LOCAL_ID",
    ];
    protected $casts = [
        "PUBLICO_ID" => "integer",
        "PUBLICO_ULTIMO" => "integer",
        "LOCAL_ID" => "integer",
    ];

    public static function getByLocalId($localId) {
        return self::with([])->where("LOCAL_ID", $localId)->get();
    }
}
