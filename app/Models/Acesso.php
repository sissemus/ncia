<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Acesso extends Model
{
    use HasFactory;

    protected $table = "ACESSO";
    protected $primaryKey = "ACESSO_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;
    protected $fillable = [
        "APLICACAO_ID",
        "PERFIL_ID",
        "ACESSO_ATIVO",
    ];
    protected $casts = [
        "ACESSO_ID" => "integer",
        "APLICACAO_ID" => "integer",
        "PERFIL_ID" => "integer",
        "ACESSO_ATIVO" => "integer",
    ];

    public function aplicacao()
    {
        return $this->hasOne(Aplicacao::class, "APLICACAO_ID", "APLICACAO_ID");
    }

    public function perfil()
    {
        return $this->hasOne(Perfil::class, "PERFIL_ID", "PERFIL_ID");
    }

    public static function deleteByPerfilId($perfilId)
    {
        self::with([])->where("PERFIL_ID", $perfilId)->delete();
    }

    public static function getByUsuarioId($usuarioId)
    {
        $aplicacaoIds = DB::select(
            "SELECT ACS.APLICACAO_ID
            FROM ACESSO ACS
            INNER JOIN APLICACAO A on A.APLICACAO_ID = ACS.APLICACAO_ID
            WHERE ACS.PERFIL_ID IN (SELECT UP.PERFIL_ID FROM USUARIO_PERFIL UP WHERE UP.USUARIO_ID = ? AND UP.USUARIO_PERFIL_ATIVO = 1)
            AND A.APLICACAO_PAI_ID IS NULL
            GROUP BY ACS.APLICACAO_ID",
            [$usuarioId]
        );
        $aplicacaoIdsArray = [];
        if (count($aplicacaoIds) > 0) {
            foreach ($aplicacaoIds as $aplicacaoId) {
                $aplicacaoIdsArray[] = $aplicacaoId->APLICACAO_ID;
            }
        }
        $aplicacoesPai = Aplicacao::with([])
            ->whereIn("APLICACAO_ID", $aplicacaoIdsArray)
            ->where('APLICACAO_URL', '!=', 'veiculo_unidade')
            ->orderBy("APLICACAO_ORDEM")
            ->get();
        if ($aplicacoesPai) {
            $aplicacoesPaiArray = $aplicacoesPai->toArray();
            for ($i = 0; $i < count($aplicacoesPaiArray); $i++) {
                $children = DB::select(
                    "SELECT ACS.APLICACAO_ID,
                    A.APLICACAO_PAI_ID
                    FROM ACESSO ACS
                    INNER JOIN APLICACAO A on A.APLICACAO_ID = ACS.APLICACAO_ID
                    WHERE ACS.PERFIL_ID IN (SELECT UP.PERFIL_ID FROM USUARIO_PERFIL UP WHERE UP.USUARIO_ID = ? AND UP.USUARIO_PERFIL_ATIVO = 1)
                    AND A.APLICACAO_PAI_ID = ?
                    GROUP BY ACS.APLICACAO_ID, A.APLICACAO_PAI_ID",
                    [$usuarioId, $aplicacoesPaiArray[$i]['APLICACAO_ID']]
                );
                $childrenArray = [];
                if ($children) {
                    foreach ($children as $child) {
                        $childrenArray[] = $child->APLICACAO_ID;
                    }
                }
                $aplicacoesPaiArray[$i]['children'] = Aplicacao::with([])
                    ->whereIn("APLICACAO_ID", $childrenArray)
                    ->where("APLICACAO_ATIVA", 1)
                    ->where('APLICACAO_URL', '!=', 'veiculo_unidade')
                    ->orderBy("APLICACAO_ORDEM")
                    ->get()
                    ->toArray();
                $aplicacoesPaiArray[$i]['model'] = false;
            }
            return $aplicacoesPaiArray;
        }
    }
}
