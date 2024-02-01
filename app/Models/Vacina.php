<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer VACINA_ID
 * @property string VACINA_NOME
 * @property string VACINA_DH_CADASTRO
 * @method static Vacina find(array|string|null $post)
 */
class Vacina extends Model {
    use HasFactory;

    protected $table = "VACINA";
    protected $primaryKey = "VACINA_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;
    protected $fillable = [
        "VACINA_NOME",
        "VACINA_DH_CADASTRO",
    ];
    protected $casts = [

    ];
}
