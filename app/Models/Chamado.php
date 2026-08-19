<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chamado extends Model
{
    protected $table = "CHAMADO";
    protected $primaryKey = "CHAMADO_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        "PACIENTE_ID",
        "TG_CHAMADO_ID",
        "TG_PRIORIDADE_ID",
        "CHAMADO_DATA",
        "CHAMADO_OBSERVACAO",
        "UNIDADE_ID_SOLICITANTE",
        "UNIDADE_ID_DESTINO",
        "CHAMADO_HORARIO_ATENDIMENTO",
        "CHAMADO_SETOR_SOLICITANTE",
        "CHAMADO_LEITO_SOLICITANTE",
        "CHAMADO_SETOR_DESTINO",
        "CHAMADO_LEITO_DESTINO",
        "CHAMADO_DISPOSITIVOS",
        "CHAMADO_PESO",
        "TG_TIPO_PRECAUCAO_ID",
        "TG_SUPORTE_O2_ID",
        "TG_SUPORTE_HEMODINAMICO_ID",
        "TG_TEMPERATURA_ID",
        "TG_FREQUENCIA_CARDIACA_ID",
        "TG_PRESSAO_ARTERIAL_ID",
        "TG_SATURACAO_ID",
        "PROFISSIONAL_ID_SOLICITANTE",
        "CHAMADO_AMBULANCIA_EXTRA",
    ];

    protected $casts = [
        "CHAMADO_ID" => "integer",
        "PACIENTE_ID" => "integer",
        "TG_CHAMADO_ID" => "integer",
        "TG_PRIORIDADE_ID" => "integer",
        "UNIDADE_ID_SOLICITANTE" => "integer",
        "UNIDADE_ID_DESTINO" => "integer",
        "CHAMADO_DATA" => "datetime",
        "CHAMADO_PESO" => "decimal:2",
        "TG_TIPO_PRECAUCAO_ID" => "integer",
        "TG_SUPORTE_O2_ID" => "integer",
        "TG_SUPORTE_HEMODINAMICO_ID" => "integer",
        "TG_TEMPERATURA_ID" => "integer",
        "TG_FREQUENCIA_CARDIACA_ID" => "integer",
        "TG_PRESSAO_ARTERIAL_ID" => "integer",
        "TG_SATURACAO_ID" => "integer",
        "PROFISSIONAL_ID_SOLICITANTE" => "integer",
        "CHAMADO_AMBULANCIA_EXTRA" => "boolean",
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, "PACIENTE_ID", "PACIENTE_ID");
    }

    public function unidadeSolicitante()
    {
        return $this->belongsTo(Unidade::class, "UNIDADE_ID_SOLICITANTE", "UNIDADE_ID");
    }

    public function unidadeDestino()
    {
        return $this->belongsTo(Unidade::class, "UNIDADE_ID_DESTINO", "UNIDADE_ID");
    }

    public function profissionalSolicitante()
    {
        return $this->belongsTo(Profissional::class, "PROFISSIONAL_ID_SOLICITANTE", "PROFISSIONAL_ID");
    }

    public function procedimentos()
    {
        return $this->belongsToMany(Procedimento::class, "CHAMADO_PROCEDIMENTO", "CHAMADO_ID", "PROCEDIMENTO_ID");
    }

    public function diagnosticos()
    {
        return $this->belongsToMany(Diagnostico::class, "CHAMADO_DIAGNOSTICO", "CHAMADO_ID", "DIAGNOSTICO_ID");
    }

    public function situacoes()
    {
        return $this->hasMany(ChamadoSituacao::class, "CHAMADO_ID", "CHAMADO_ID");
    }

    public function chamadoProcedimentos()
    {
        return $this->hasMany(ChamadoProcedimento::class, "CHAMADO_ID", "CHAMADO_ID");
    }

    public function chamadoDiagnosticos()
    {
        return $this->hasMany(ChamadoDiagnostico::class, "CHAMADO_ID", "CHAMADO_ID");
    }
}