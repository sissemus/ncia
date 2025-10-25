<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *@property string AUDITORIA_DATA
 *@property integer AUDITORIA_USUARIO_ID
 *@property string AUDITORIA_USUARIO
 *@property string AUDITORIA_TABELA
 *@property integer AUDITORIA_LINHA_ID
 *@property string AUDITORIA_CAMPO
 *@property string AUDITORIA_ANTES
 *@property string AUDITORIA_DEPOIS
 *@property string AUDITORIA_OPERACAO
 */
class Auditoria extends Model
{
    protected $table = "AUDITORIA";
    protected $primaryKey = "AUDITORIA_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;
}
