<?php

namespace App\Models;

use App\MyLibs\RTG;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Chamado extends Model
{
    protected $table = "CHAMADO";
    protected $primaryKey = "CHAMADO_ID";
    public $timestamps = false;

    protected $fillable = [
        "PACIENTE_ID",
        "TG_CHAMADO_ID",
        "TG_PRIORIDADE_ID",
        "CHAMADO_DATA",
        "DIAGNOSTICO_ID",
        "LOCALIDADE_ID_ORIGEM",
        "LOCALIDADE_ID_DESTINO",
        "CHAMADO_OBSERVACAO",
        "CHAMADO_RETORNO_IMEDIATO",
        "CHAMADO_ID_ORIGEM",
        "CHAMADO_ID_PAI",
    ];

    protected $casts = [
        "PACIENTE_ID" => "integer",
        "TG_CHAMADO_ID" => "integer",
        "TG_PRIORIDADE_ID" => "integer",
        "CHAMADO_DATA" => "datetime",
        "DIAGNOSTICO_ID" => "integer",
        "LOCALIDADE_ID_ORIGEM" => "integer",
        "LOCALIDADE_ID_DESTINO" => "integer",
        "CHAMADO_OBSERVACAO" => "string",
        "CHAMADO_RETORNO_IMEDIATO" => "integer",
        "CHAMADO_ID_ORIGEM" => "integer",
        "CHAMADO_ID_PAI" => "integer",
    ];

    public function paciente(){
        return $this->hasOne(Paciente::class, 'PACIENTE_ID', 'PACIENTE_ID');
    }

    public function diagnostico(){
        return $this->hasOne(Diagnostico::class, 'DIAGNOSTICO_ID', 'DIAGNOSTICO_ID');
    }

    public function localidadeOrigem(){
        return $this->hasOne(Unidade::class, 'UNIDADE_ID', 'LOCALIDADE_ID_ORIGEM');
    }

    public function localidadeDestino(){
        return $this->hasOne(Unidade::class, 'UNIDADE_ID', 'LOCALIDADE_ID_DESTINO');
    }

    public static function pesquisar($requisicao)
    {
        return self::with([
                'paciente',
                'diagnostico',
                'localidadeOrigem',
                'localidadeDestino',
            ])
            ->when($requisicao->CHAMADO_ID, function (Builder $query) use ($requisicao) {
                return $query->where("CHAMADO_ID", "=", $requisicao->CHAMADO_ID);
            })
            ->when($requisicao->PACIENTE_ID, function (Builder $query) use ($requisicao) {
                return $query->where("PACIENTE_ID", "=", $requisicao->PACIENTE_ID);
            })
            ->when($requisicao->LOCALIDADE_ID_ORIGEM, function (Builder $query) use ($requisicao) {
                return $query->where("LOCALIDADE_ID_ORIGEM", "=", $requisicao->LOCALIDADE_ID_ORIGEM);
            })
            ->when($requisicao->LOCALIDADE_ID_DESTINO, function (Builder $query) use ($requisicao) {
                return $query->where("LOCALIDADE_ID_DESTINO", "=", $requisicao->LOCALIDADE_ID_DESTINO);
            })
            ->orderBy('PACIENTE_ID')
            ->paginate();
    }

    public static function buscar($id)
    {
        return self::find($id);
    }
}
