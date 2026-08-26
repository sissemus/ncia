<?php

namespace App\Models;

use App\MyLibs\RTG;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public static function pesquisarNUsados($requisicao)
    {
        $hoje = Carbon::today();

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
            ->whereNotExists(function($q) use ($hoje){
                return $q->select(DB::raw(1))
                ->from('EQUIPE_PROFISSIONAL as ep')
                ->join('EQUIPE as e', 'e.EQUIPE_ID', '=', 'ep.EQUIPE_ID')
                ->whereColumn('ep.PROFISSIONAL_ID', 'PROFISSIONAL.PROFISSIONAL_ID')
                ->whereDate('e.EQUIPE_DATA', $hoje)
                ->where('e.EQUIPE_ATIVO', 1);
            })
            ->orderBy('PROFISSIONAL_ID')
            ->get();
    }

    public static function buscar($id)
    {
        return self::find($id);
    }
}
