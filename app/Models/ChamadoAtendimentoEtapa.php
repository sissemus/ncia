<?php

namespace App\Models;

use App\MyLibs\EtapaAtendimentoEnum;
use Illuminate\Database\Eloquent\Model;

class ChamadoAtendimentoEtapa extends Model
{
    protected $table = 'CHAMADO_ATENDIMENTO_ETAPA';
    protected $primaryKey = 'CHAMADO_ATENDIMENTO_ETAPA_ID';
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        'CHAMADO_ID', 'CHAMADO_EQUIPE_ID', 'ATENDIMENTO_ETAPA_ID',
        'ATENDIMENTO_ETAPA_DATA', 'USUARIO_ID',
    ];

    protected $casts = [
        'CHAMADO_ATENDIMENTO_ETAPA_ID' => 'integer',
        'CHAMADO_ID' => 'integer',
        'CHAMADO_EQUIPE_ID' => 'integer',
        'ATENDIMENTO_ETAPA_ID' => 'integer',
        'ATENDIMENTO_ETAPA_DATA' => 'datetime',
        'USUARIO_ID' => 'integer',
    ];

    protected $appends = ['ATENDIMENTO_ETAPA_DESCRICAO'];

    public function getAtendimentoEtapaDescricaoAttribute()
    {
        return EtapaAtendimentoEnum::descricao($this->ATENDIMENTO_ETAPA_ID);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'USUARIO_ID', 'USUARIO_ID');
    }

    public function vinculoEquipe()
    {
        return $this->belongsTo(ChamadoEquipe::class, 'CHAMADO_EQUIPE_ID', 'CHAMADO_EQUIPE_ID');
    }
}
