<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtualizacaoClinica extends Model
{
    protected $table = "ATUALIZACAO_CLINICA";
    protected $primaryKey = "ATUALIZACAO_CLINICA_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        "CHAMADO_ID",
        "TG_PRIORIDADE_ANTERIOR_ID",
        "TG_PRIORIDADE_ID",
        "TG_TIPO_PRECAUCAO_ID",
        "TG_SUPORTE_O2_ID",
        "TG_SUPORTE_HEMODINAMICO_ID",
        "ATUALIZACAO_CLINICA_TEMPERATURA",
        "ATUALIZACAO_CLINICA_PRESSAO_ARTERIAL",
        "ATUALIZACAO_CLINICA_FREQUENCIA_CARDIACA",
        "ATUALIZACAO_CLINICA_SATURACAO_O2",
        "ATUALIZACAO_CLINICA_ESCALA_GLASGOW",
        "ATUALIZACAO_CLINICA_PROFISSIONAL",
        "ATUALIZACAO_CLINICA_CONSELHO",
        "ATUALIZACAO_CLINICA_NUMERO_CONSELHO",
        "ATUALIZACAO_CLINICA_JUSTIFICATIVA_MEDICA",
        "USUARIO_ID",
        "ATUALIZACAO_CLINICA_DATA",
    ];

    protected $casts = [
        "ATUALIZACAO_CLINICA_ID" => "integer",
        "CHAMADO_ID" => "integer",
        "TG_PRIORIDADE_ANTERIOR_ID" => "integer",
        "TG_PRIORIDADE_ID" => "integer",
        "TG_TIPO_PRECAUCAO_ID" => "integer",
        "TG_SUPORTE_O2_ID" => "integer",
        "TG_SUPORTE_HEMODINAMICO_ID" => "integer",
        "USUARIO_ID" => "integer",
        "ATUALIZACAO_CLINICA_DATA" => "datetime",
    ];

    public function chamado()
    {
        return $this->belongsTo(Chamado::class, "CHAMADO_ID", "CHAMADO_ID");
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, "USUARIO_ID", "USUARIO_ID");
    }
}
