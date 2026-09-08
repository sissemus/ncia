<?php

namespace App\Models;

use App\MyLibs\RTG;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Equipe extends Model
{
    protected $table = "EQUIPE";
    protected $primaryKey = "EQUIPE_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        "VEICULO_ID",
        "EQUIPE_DATA",
        "EQUIPE_TURNO",
        "EQUIPE_ATIVO",
        "USUARIO_ID_CAD",
    ];

    protected $casts = [
        "EQUIPE_ID" => "integer",
        "VEICULO_ID" => "integer",
        "EQUIPE_DATA" => "date",
        "EQUIPE_TURNO" => "string",
        "EQUIPE_ATIVO" => "integer",
        "USUARIO_ID_CAD" => "integer",
    ];

    public function veiculo(){
        return $this->hasOne(Veiculo::class, 'VEICULO_ID', 'VEICULO_ID');
    }

    public function equipeProfissional(){
        return $this->hasMany(EquipeProfissional::class, 'EQUIPE_ID', 'EQUIPE_ID');
    }

    public static function pesquisar($requisicao)
    {
        return self::with([
                'veiculo',
                'equipeProfissional',
                'equipeProfissional.profissional',
                'equipeProfissional.profissional.tipoProfissional'
            ])
            ->when($requisicao->EQUIPE_ID, function (Builder $query) use ($requisicao) {
                return $query->where("EQUIPE_ID", "=", $requisicao->EQUIPE_ID);
            })
            ->when($requisicao->VEICULO_ID, function (Builder $query) use ($requisicao) {
                return $query->where("VEICULO_ID", "=", $requisicao->VEICULO_ID);
            })
            ->orderBy('VEICULO_ID')
            ->paginate();
    }

    public static function buscar($id)
    {
        return self::find($id);
    }
}
