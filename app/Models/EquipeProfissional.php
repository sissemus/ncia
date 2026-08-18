<?php

namespace App\Models;

use App\MyLibs\RTG;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EquipeProfissional extends Model
{
    protected $table = "EQUIPE_PROFISSIONAL";
    protected $primaryKey = "EQUIPE_PROFISSIONAL_ID";
    public $timestamps = false;

    protected $fillable = [
        "EQUIPE_ID",
        "PROFISSIONAL_ID",
        "EQUIPE_PROFISSIONAL_ATIVO",
    ];

    protected $casts = [
        "EQUIPE_PROFISSIONAL_ID" => "integer",
        "EQUIPE_ID" => "integer",
        "PROFISSIONAL_ID" => "integer",
        "EQUIPE_PROFISSIONAL_ATIVO" => "integer"
    ];

    public function profissional(){
        return $this->hasOne(Profissional::class, 'PROFISSIONAL_ID', 'PROFISSIONAL_ID');
    }

    public static function pesquisar($requisicao)
    {
        return self::with([
                'profissional',
                'profissional.tipoProfissional'
            ])
            ->when($requisicao->EQUIPE_ID, function (Builder $query) use ($requisicao) {
                return $query->where("EQUIPE_ID", "=", $requisicao->EQUIPE_ID);
            })
            ->when($requisicao->PROFISSIONAL_ID, function (Builder $query) use ($requisicao) {
                return $query->where("PROFISSIONAL_ID", "=", $requisicao->PROFISSIONAL_ID);
            })
            ->orderBy('PROFISSIONAL_ID')
            ->paginate();
    }

    public static function buscar($id)
    {
        return self::find($id);
    }
}
