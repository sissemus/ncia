<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        $aplicacaoIdsArray = self::aplicacoesAtivasDoUsuario($usuarioId)
            ->whereNull('a.APLICACAO_PAI_ID')
            ->distinct()
            ->pluck('a.APLICACAO_ID');

        $aplicacoesPai = Aplicacao::with([])
            ->whereIn("APLICACAO_ID", $aplicacaoIdsArray)
            ->where('APLICACAO_ATIVA', 1)
            ->orderBy("APLICACAO_ORDEM")
            ->get();
        if ($aplicacoesPai) {
            $visibilidadeNoCadastro = Schema::hasColumn('APLICACAO', 'APLICACAO_EXIBIR_MENU');
            $aplicacoesPaiFiltered = $aplicacoesPai->filter(function ($app) {
                return $app->APLICACAO_URL !== 'veiculo_unidade';
            });
            $aplicacoesPaiArray = [];
            foreach ($aplicacoesPaiFiltered as $appPai) {
                $appPaiArray = $appPai->toArray();
                $childrenArray = self::aplicacoesAtivasDoUsuario($usuarioId)
                    ->where('a.APLICACAO_PAI_ID', $appPaiArray['APLICACAO_ID'])
                    ->distinct()
                    ->pluck('a.APLICACAO_ID');
                $childrenQuery = Aplicacao::with([])
                    ->whereIn("APLICACAO_ID", $childrenArray)
                    ->where("APLICACAO_ATIVA", 1)
                    ->orderBy("APLICACAO_ORDEM");
                if ($visibilidadeNoCadastro) {
                    $childrenQuery->where('APLICACAO_EXIBIR_MENU', 1);
                } else {
                    $childrenQuery->where('APLICACAO_URL', '<>', 'chamado_analisar');
                }
                $childrenApps = $childrenQuery->get();
                
                $appPaiArray['children'] = $childrenApps->filter(function ($app) {
                    return $app->APLICACAO_URL !== 'veiculo_unidade';
                })->values()->toArray();
                
                $appPaiArray['model'] = false;
                $aplicacoesPaiArray[] = $appPaiArray;
            }
            return $aplicacoesPaiArray;
        }

        return [];
    }

    public static function usuarioPossuiAplicacao($usuarioId, $url)
    {
        return self::aplicacoesAtivasDoUsuario($usuarioId)
            ->whereIn('a.APLICACAO_URL', [$url, $url . '/view'])
            ->exists();
    }

    private static function aplicacoesAtivasDoUsuario($usuarioId)
    {
        return DB::table('ACESSO as acs')
            ->join('APLICACAO as a', 'a.APLICACAO_ID', '=', 'acs.APLICACAO_ID')
            ->join('USUARIO_PERFIL as up', 'up.PERFIL_ID', '=', 'acs.PERFIL_ID')
            ->where('up.USUARIO_ID', $usuarioId)
            ->where('up.USUARIO_PERFIL_ATIVO', 1)
            ->where('acs.ACESSO_ATIVO', 1)
            ->where('a.APLICACAO_ATIVA', 1);
    }
}
