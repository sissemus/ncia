<?php

namespace App\Models;

use App\Models\Concerns\PertenceAViagem;
use App\MyLibs\SituacaoChamadoEnum;
use Illuminate\Database\Eloquent\Model;

class ChamadoSituacao extends Model
{
    use PertenceAViagem;

    protected static function boot()
    {
        parent::boot();
        static::created(function ($situacao) {
            if (!$situacao->CHAMADO_VIAGEM_ID) return;
            $viagem = ChamadoViagem::find($situacao->CHAMADO_VIAGEM_ID);
            if (!$viagem) return;
            $viagem->TG_SITUACAO_ID = $situacao->TG_SITUACAO_ID;
            if (in_array((int) $situacao->TG_SITUACAO_ID, [SituacaoChamadoEnum::CONCLUIDO, SituacaoChamadoEnum::CANCELADO], true)) {
                $viagem->FIM = $situacao->CHAMADO_SITUACAO_DATA;
            }
            $viagem->save();
        });
    }
    protected $table = "CHAMADO_SITUACAO";
    protected $primaryKey = "CHAMADO_SITUACAO_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        "TG_SITUACAO_ID",
        "CHAMADO_ID",
        "CHAMADO_VIAGEM_ID",
        "CHAMADO_SITUACAO_DATA",
        "CHAMADO_SITUACAO_OBSERVACAO",
        "USUARIO_ID",
    ];

    protected $casts = [
        "CHAMADO_SITUACAO_ID" => "integer",
        "CHAMADO_VIAGEM_ID" => "integer",
        "TG_SITUACAO_ID" => "integer",
        "CHAMADO_ID" => "integer",
        "CHAMADO_SITUACAO_DATA" => "datetime",
        "USUARIO_ID" => "integer",
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
