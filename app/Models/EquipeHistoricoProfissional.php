<?php

namespace App\Models;

use App\MyLibs\RTG;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EquipeHistoricoProfissional extends Model
{
    protected $table = "EQUIPE_HISTORICO_PROFISSIONAL";
    protected $primaryKey = "EQUIPE_PROFISSIONAL_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        "EQUIPE_ID",
        "PROFISSIONAL_ID",
        "EQUIPE_PROFISSIONAL_ATIVO",
        "USUARIO_ID_CAD",
    ];

    protected $casts = [
        "EQUIPE_PROFISSIONAL_ID" => "integer",
        "EQUIPE_ID" => "integer",
        "PROFISSIONAL_ID" => "integer",
        "EQUIPE_PROFISSIONAL_ATIVO" => "integer",
        "USUARIO_ID_CAD" => "integer",
    ];

    public function equipeHistorico(){
        return $this->hasOne(EquipeHistorico::class, 'EQUIPE_ID', 'EQUIPE_ID');
    }

    public function profissional(){
        return $this->hasOne(Profissional::class, 'PROFISSIONAL_ID', 'PROFISSIONAL_ID');
    }

    public static function pesquisar($requisicao)
    {
        return self::with([
                'equipeHistorico',
                'equipeHistorico.profissional',
                'equipeHistorico.profissional.tipoProfissional',
            ])
            ->when($requisicao->EQUIPE_PROFISSIONAL_ID, function (Builder $query) use ($requisicao) {
                return $query->where("EQUIPE_PROFISSIONAL_ID", "=", $requisicao->EQUIPE_PROFISSIONAL_ID);
            })
            ->when($requisicao->EQUIPE_ID, function (Builder $query) use ($requisicao) {
                return $query->where("EQUIPE_ID", "=", $requisicao->EQUIPE_ID);
            })
            ->orderBy('EQUIPE_ID')
            ->paginate();
    }

    public static function buscar($id)
    {
        return self::find($id);
    }
}
