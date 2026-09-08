<?php

namespace App\Models;

use App\MyLibs\RTG;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ChamadoEquipe extends Model
{
    protected $table = "CHAMADO_EQUIPE";
    protected $primaryKey = "CHAMADO_EQUIPE_ID";
    public $timestamps = false;

    protected $fillable = [
        "CHAMADO_ID",
        "EQUIPE_ID",
        "CHAMADO_EQUIPE_ATIVO",
    ];

    protected $casts = [
        "CHAMADO_EQUIPE_ID" => "integer",
        "CHAMADO_ID" => "integer",
        "EQUIPE_ID" => "integer",
        "CHAMADO_EQUIPE_ATIVO" => "integer",
    ];

    public function equipe(){
        return $this->hasOne(Equipe::class, 'EQUIPE_ID', 'EQUIPE_ID');
    }

    public function chamado(){
        return $this->hasOne(Chamado::class, 'CHAMADO_ID', 'CHAMADO_ID');
    }

    public static function pesquisar($requisicao)
    {
        return self::with([
                'chamado',
                'equipe'
            ])
            ->when($requisicao->CHAMADO_EQUIPE_ID, function (Builder $query) use ($requisicao) {
                return $query->where("CHAMADO_EQUIPE_ID", "=", $requisicao->CHAMADO_EQUIPE_ID);
            })
            ->when($requisicao->EQUIPE_ID, function (Builder $query) use ($requisicao) {
                return $query->where("EQUIPE_ID", "=", $requisicao->EQUIPE_ID);
            })
            ->when($requisicao->CHAMADO_ID, function (Builder $query) use ($requisicao) {
                return $query->where("CHAMADO_ID", "=", $requisicao->CHAMADO_ID);
            })
            ->orderBy('CHAMADO_EQUIPE_ID')
            ->paginate();
    }

    public static function buscar($id)
    {
        return self::find($id);
    }
}
