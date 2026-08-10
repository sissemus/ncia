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

    protected $fillable = [
        "VEICULO_ID",
        "PROFISSIONAL_ID",
        "EQUIPE_ATIVO",
    ];

    protected $casts = [
        "EQUIPE_ID" => "integer",
        "VEICULO_ID" => "integer",
        "PROFISSIONAL_ID" => "integer",
        "EQUIPE_ATIVO" => "integer",
    ];

    public function veiculo(){
        return $this->hasOne(Veiculo::class, 'VEICULO_ID', 'VEICULO_ID');
    }

    public function profissional(){
        return $this->hasOne(Profissional::class, 'PROFISSIONAL_ID', 'PROFISSIONAL_ID');
    }

    public static function pesquisar($requisicao)
    {
        return self::with([
                'veiculo',
                'profissional',
                'profissional.tipoProfissional'
            ])
            ->when($requisicao->EQUIPE_ID, function (Builder $query) use ($requisicao) {
                return $query->where("EQUIPE_ID", "=", $requisicao->EQUIPE_ID);
            })
            ->when($requisicao->VEICULO_ID, function (Builder $query) use ($requisicao) {
                return $query->where("VEICULO_ID", "=", $requisicao->VEICULO_ID);
            })
            ->when($requisicao->PROFISSIONAL_ID, function (Builder $query) use ($requisicao) {
                return $query->where("PROFISSIONAL_ID", "=", $requisicao->EQUIPE_ID);
            })
            ->orderBy('VEICULO_ID')
            ->paginate();
    }

    public static function buscar($id)
    {
        return self::find($id);
    }
}
