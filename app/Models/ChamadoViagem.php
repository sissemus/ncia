<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChamadoViagem extends Model
{
    protected $table = 'CHAMADO_VIAGEM';
    protected $primaryKey = 'CHAMADO_VIAGEM_ID';
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        'CHAMADO_ID', 'NUMERO', 'RETORNO', 'TG_CHAMADO_ID',
        'UNIDADE_ID_SOLICITANTE', 'TG_SITUACAO_ID', 'INICIO', 'FIM', 'DADOS',
    ];

    protected $casts = [
        'CHAMADO_VIAGEM_ID' => 'integer', 'CHAMADO_ID' => 'integer',
        'NUMERO' => 'integer', 'RETORNO' => 'boolean',
        'TG_CHAMADO_ID' => 'integer', 'UNIDADE_ID_SOLICITANTE' => 'integer', 'TG_SITUACAO_ID' => 'integer',
        'INICIO' => 'datetime', 'FIM' => 'datetime', 'DADOS' => 'array',
    ];

    public function situacoes()
    {
        return $this->hasMany(ChamadoSituacao::class, 'CHAMADO_VIAGEM_ID', 'CHAMADO_VIAGEM_ID')
            ->orderBy('CHAMADO_SITUACAO_DATA')->orderBy('CHAMADO_SITUACAO_ID');
    }

    public function etapasAtendimento()
    {
        return $this->hasMany(ChamadoAtendimentoEtapa::class, 'CHAMADO_VIAGEM_ID', 'CHAMADO_VIAGEM_ID')
            ->orderBy('ATENDIMENTO_ETAPA_ID');
    }

    public function atualizacoesClinicas()
    {
        return $this->hasMany(AtualizacaoClinica::class, 'CHAMADO_VIAGEM_ID', 'CHAMADO_VIAGEM_ID');
    }

    public function vinculosEquipe()
    {
        return $this->hasMany(ChamadoEquipe::class, 'CHAMADO_VIAGEM_ID', 'CHAMADO_VIAGEM_ID');
    }
}
