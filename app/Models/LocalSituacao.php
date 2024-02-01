<?php

namespace App\Models;

use App\Enums\LocalSituacaoEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property integer LOCAL_SITUACAO_ID
 * @property string LOCAL_SITUACAO_DATA
 * @property integer LOCAL_ID
 * @property integer SITUACAO_ID
 * @property integer LOCAL_SITUACAO_ULTIMO
 */
class LocalSituacao extends Model {
    use HasFactory;

    protected $table = "LOCAL_SITUACAO";
    protected $primaryKey = "LOCAL_SITUACAO_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;
    protected $fillable = [
        "LOCAL_SITUACAO_DATA",
        "LOCAL_ID",
        "SITUACAO_ID",
    ];
    protected $casts = [
        "LOCAL_SITUACAO_ID" => "integer",
        "LOCAL_ID" => "integer",
        "SITUACAO_ID" => "integer",
        "LOCAL_SITUACAO_ULTIMO" => "integer",
    ];
    public static $relacionamentos = [
        "situacao"
    ];

    public function local() {
        return $this->hasOne(Local::class, "LOCAL_ID", "LOCAL_ID");
    }

    public function situacao() {
        return $this->hasOne(Situacao::class, "SITUACAO_ID", "SITUACAO_ID");
    }

    public static function getByLocalId($localId) {
        return self::with(self::$relacionamentos)
            ->where("LOCAL_ID", $localId)
            ->orderBy("LOCAL_SITUACAO_DATA", "desc")
            ->paginate();
    }

    public static function mediaGeralPorLocalTipo($localTipo) {
        return DB::select("
                                    SELECT TOP 1 S.SITUACAO_ID, S.SITUACAO_NOME, COUNT(LS.SITUACAO_ID) TOTAL, S.SITUACAO_ESCALA
                                    FROM LOCAL_SITUACAO LS
                                             INNER JOIN SITUACAO S on S.SITUACAO_ID = LS.SITUACAO_ID
                                             INNER JOIN LOCAL L on L.LOCAL_ID = LS.LOCAL_ID
                                    WHERE LS.LOCAL_SITUACAO_ULTIMO = 1
                                      AND L.LOCAL_TIPO = $localTipo
                                    GROUP BY S.SITUACAO_ID, S.SITUACAO_NOME, S.SITUACAO_ESCALA
                                    ORDER BY TOTAL DESC, S.SITUACAO_ESCALA
                                "
        );
    }

    public static function mediaGeralTotal() {
        return DB::select("
                                    SELECT TOP 1 S.SITUACAO_ID, S.SITUACAO_NOME, COUNT(LS.SITUACAO_ID) TOTAL, S.SITUACAO_ESCALA
                                    FROM LOCAL_SITUACAO LS
                                             INNER JOIN SITUACAO S on S.SITUACAO_ID = LS.SITUACAO_ID
                                             INNER JOIN LOCAL L on L.LOCAL_ID = LS.LOCAL_ID
                                    WHERE LS.LOCAL_SITUACAO_ULTIMO = 1
                                    GROUP BY S.SITUACAO_ID, S.SITUACAO_NOME, S.SITUACAO_ESCALA
                                    ORDER BY TOTAL DESC, S.SITUACAO_ESCALA
                                "
        );
    }

    public static function totalPostosAtivos() {
        return self::with(["local"])
            ->where("LOCAL_SITUACAO_ULTIMO", 1)
            ->where("SITUACAO_ID", "!=", LocalSituacaoEnum::ENCERRADO)
            ->whereHas("local", function ($q) {
                $q->where("LOCAL_ATIVO", 1);
            })
            ->count();
    }

    public static function existeEmpateLocalTipo($localTipo) {
        $total = DB::select("
                                SELECT COUNT(rank) AS TOTAL
                                FROM (
                                         SELECT SITUACAO_NOME, rank() over (partition by 1 order by count(0) desc) as rank
                                         FROM dbo.LOCAL_SITUACAO ls,
                                              dbo.SITUACAO s,
                                              dbo.LOCAL l
                                         WHERE ls.LOCAL_ID = l.LOCAL_ID
                                           AND ls.SITUACAO_ID = s.SITUACAO_ID
                                           AND LOCAL_SITUACAO_ULTIMO = 1
                                         AND l.LOCAL_TIPO = $localTipo
                                         GROUP BY SITUACAO_NOME
                                     ) t
                                where rank = 1
                                "
        );
        return $total[0]->TOTAL > 1;
    }

    public static function existeEmpateGeral() {
        $total = DB::select("
                                SELECT COUNT(rank) AS TOTAL
                                FROM (
                                         SELECT SITUACAO_NOME, rank() over (partition by 1 order by count(0) desc) as rank
                                         FROM dbo.LOCAL_SITUACAO ls,
                                              dbo.SITUACAO s,
                                              dbo.LOCAL l
                                         WHERE ls.LOCAL_ID = l.LOCAL_ID
                                           AND ls.SITUACAO_ID = s.SITUACAO_ID
                                           AND LOCAL_SITUACAO_ULTIMO = 1
                                         GROUP BY SITUACAO_NOME
                                     ) t
                                where rank = 1
                                "
        );

        return $total[0]->TOTAL > 1;
    }

    public static function getGraficoDoDia($localId) {
        $dataCarbon = new Carbon();
        return DB::select("
            SELECT CONVERT(INT,SUBSTRING(CONVERT(VARCHAR,LOCAL_SITUACAO_DATA,114),1,2)) HORA,MAX(SITUACAO_ID) SITUACAO_ID
            FROM [FILOMETRO].[dbo].[LOCAL_SITUACAO]
            WHERE YEAR(LOCAL_SITUACAO_DATA) = {$dataCarbon->format('Y')}
              AND MONTH(LOCAL_SITUACAO_DATA) = {$dataCarbon->format('m')}
              AND DAY(LOCAL_SITUACAO_DATA) = {$dataCarbon->format('d')}
              AND LOCAL_ID = $localId
            GROUP BY LOCAL_ID,CONVERT(INT,SUBSTRING(CONVERT(VARCHAR,LOCAL_SITUACAO_DATA,114),1,2))
            ORDER BY 1,2
        ");
//        return DB::select("
//            SELECT CONVERT(INT,SUBSTRING(CONVERT(VARCHAR,LOCAL_SITUACAO_DATA,114),1,2)) HORA,MAX(SITUACAO_ID) SITUACAO_ID
//            FROM [FILOMETRO].[dbo].[LOCAL_SITUACAO]
//            WHERE YEAR(LOCAL_SITUACAO_DATA) = 2021
//              AND MONTH(LOCAL_SITUACAO_DATA) = 6
//              AND DAY(LOCAL_SITUACAO_DATA) = 1
//              AND LOCAL_ID = $localId
//            GROUP BY LOCAL_ID,CONVERT(INT,SUBSTRING(CONVERT(VARCHAR,LOCAL_SITUACAO_DATA,114),1,2))
//            ORDER BY 1,2
//        ");
    }

    public static function getGraficoByDataEspecifica($localId, $date) {
        $dataCarbon = new Carbon($date);
        return DB::select("
            SELECT CONVERT(INT,SUBSTRING(CONVERT(VARCHAR,LOCAL_SITUACAO_DATA,114),1,2)) HORA,MAX(SITUACAO_ID) SITUACAO_ID
            FROM [FILOMETRO].[dbo].[LOCAL_SITUACAO]
            WHERE YEAR(LOCAL_SITUACAO_DATA) = {$dataCarbon->format('Y')}
              AND MONTH(LOCAL_SITUACAO_DATA) = {$dataCarbon->format('m')}
              AND DAY(LOCAL_SITUACAO_DATA) = {$dataCarbon->format('d')}
              AND LOCAL_ID = $localId
              AND SITUACAO_ID != 5
            GROUP BY LOCAL_ID,CONVERT(INT,SUBSTRING(CONVERT(VARCHAR,LOCAL_SITUACAO_DATA,114),1,2))
            ORDER BY 1,2        
        ");
    }
}
