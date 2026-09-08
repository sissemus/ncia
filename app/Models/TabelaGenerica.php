<?php

namespace App\Models;

use App\MyLibs\RTG;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TabelaGenerica extends Model
{
    protected $table = "TABELA_GENERICA";
    protected $primaryKey = "TABELA_GENERICA_ID";
    public $timestamps = false;

    protected $fillable = [
        "TABELA_ID",
        "COLUNA_ID",
        "DESCRICAO",
        "ATIVO",
    ];

    protected $casts = [
        "TABELA_ID" => "integer",
        "COLUNA_ID" => "integer",
        "ATIVO" => "integer"
    ];

    public function tabela_generica()
    {
        return $this->hasOne(TabelaGenerica::class, "TABELA_GENERICA_ID", "TABELA_ID");
    }

    public function tabela()
    {
        return $this->hasOne(TabelaGenerica::class, "TABELA_ID", "TABELA_ID")->where("COLUNA_ID", 0);
    }

    public static function relacionamento()
    {
        return [
            "tabela_generica"
        ];
    }

    public static function listarTabelas()
    {
        return self::with([])->where("COLUNA_ID", 0)->get();
    }

    public static function listarColunasTabela($tabelaId, $somenteAtivos = 0, $with = [], $campoOrderBy = "COLUNA_ID", $direcao = "asc")
    {
        return self::with($with)
            ->where("TABELA_ID", "=", $tabelaId)
            ->where("COLUNA_ID", "!=", 0)
            ->when($somenteAtivos == 1, function ($q) {
                return $q->where("ATIVO", 1);
            })
            ->when($campoOrderBy, function ($q) use ($campoOrderBy, $direcao) {
                return $q->orderBy($campoOrderBy, $direcao);
            })->get();
    }

    public static function getColunaId($tabelaId, $colunaId)
    {
        return self::where("TABELA_ID", "=", $tabelaId)
            ->where("TABELA_GENERICA_ID", "=", $colunaId)
            ->get();
    }

    public static function listar()
    {
        return self::with(self::relacionamento())->paginate();
    }

    public static function pesquisar($requisicao)
    {
        return self::with(self::relacionamento())
            ->when($requisicao->DESCRICAO, function (Builder $query) use ($requisicao) {
                return $query->where("DESCRICAO", "like", "%$requisicao->DESCRICAO%");
            })
            ->when($requisicao->TABELA_ID, function (Builder $query) use ($requisicao) {
                return $query->where("TABELA_ID", "=", $requisicao->TABELA_ID);
            })
            ->when($requisicao->ATIVO, function (Builder $query) use ($requisicao) {
                return $query->where("ATIVO", "=", $requisicao->ATIVO);
            })
            ->get();
    }

    public static function buscar($id)
    {
        return self::with(self::relacionamento())->find($id);
    }

    public static function obterUltimoIdDeTabela()
    {
        return DB::table("TABELA_GENERICA")
            ->select([DB::raw("MAX(TABELA_ID) AS TABELA_ID")])
            ->where("COLUNA_ID", 0)
            ->pluck("TABELA_ID")
            ->first();
    }

    public static function obterUltimoIdDeColuna($tabelaId)
    {
        return DB::table("TABELA_GENERICA")
            ->select([DB::raw("MAX(COLUNA_ID) AS COLUNA_ID")])
            ->where("TABELA_ID", $tabelaId)
            ->where("COLUNA_ID", ">", 0)
            ->pluck("COLUNA_ID")
            ->first();
    }

    // TABELAS

    public static function tabelaGenerica($colunaId = null)
    {
        $tabela = RTG::TABELA_GENERICA;
        if ($colunaId) return self::getColunaId($tabela, $colunaId);
        return self::listarColunasTabela($tabela);
    }

    public static function hierarquia($colunaId = null)
    {
        $tabela = RTG::HIERARQUIA;
        if ($colunaId) return self::getColunaId($tabela, $colunaId);
        return self::listarColunasTabela($tabela);
    }

    public static function sexo($colunaId = null)
    {
        $tabela = RTG::SEXO;
        if ($colunaId) return self::getColunaId($tabela, $colunaId);
        return self::listarColunasTabela($tabela);
    }

    public static function tipoProfissional($colunaId = null)
    {
        $tabela = RTG::TIPO_PROFISSIONAL;
        if ($colunaId) return self::getColunaId($tabela, $colunaId);
        return self::listarColunasTabela($tabela);
    }

    public static function tipoVeiculo($colunaId = null)
    {
        $tabela = RTG::TIPO_VEICULO;
        if ($colunaId) return self::getColunaId($tabela, $colunaId);
        return self::listarColunasTabela($tabela, 1);
    }

    public static function situacaoVeiculo($colunaId = null)
    {
        $tabela = RTG::SITUACAO_VEICULO;
        if ($colunaId) return self::getColunaId($tabela, $colunaId);
        return self::listarColunasTabela($tabela, 1);
    }

    public static function prioridadePaciente($colunaId = null)
    {
        $tabela = RTG::PRIORIDADE_PACIENTE;
        if ($colunaId) return self::getColunaId($tabela, $colunaId);
        return self::listarColunasTabela($tabela, 1);
    }

    public static function tipoChamado($colunaId = null)
    {
        $tabela = RTG::TIPO_CHAMADO;
        if ($colunaId) return self::getColunaId($tabela, $colunaId);
        return self::listarColunasTabela($tabela, 1);
    }

    public static function tipoPrecaucao($colunaId = null)
    {
        $tabela = RTG::TIPO_PRECAUCAO;
        if ($colunaId) return self::getColunaId($tabela, $colunaId);
        return self::listarColunasTabela($tabela, 1);
    }

    public static function suporteO2($colunaId = null)
    {
        $tabela = RTG::SUPORTE_O2;
        if ($colunaId) return self::getColunaId($tabela, $colunaId);
        return self::listarColunasTabela($tabela, 1);
    }

    public static function suporteHemodinamico($colunaId = null)
    {
        $tabela = RTG::SUPORTE_HEMODINAMICO;
        if ($colunaId) return self::getColunaId($tabela, $colunaId);
        return self::listarColunasTabela($tabela, 1);
    }

    public static function sinaisVitaisTemperatura($colunaId = null)
    {
        $tabela = RTG::SINAIS_VITAIS_TEMPERATURA;
        if ($colunaId) return self::getColunaId($tabela, $colunaId);
        return self::listarColunasTabela($tabela, 1);
    }

    public static function sinaisVitaisFrequenciaCardiaca($colunaId = null)
    {
        $tabela = RTG::SINAIS_VITAIS_FREQUENCIA_CARDIACA;
        if ($colunaId) return self::getColunaId($tabela, $colunaId);
        return self::listarColunasTabela($tabela, 1);
    }

    public static function sinaisVitaisPressaoArterial($colunaId = null)
    {
        $tabela = RTG::SINAIS_VITAIS_PRESSAO_ARTERIAL;
        if ($colunaId) return self::getColunaId($tabela, $colunaId);
        return self::listarColunasTabela($tabela, 1);
    }

    public static function sinaisVitaisSaturacao($colunaId = null)
    {
        $tabela = RTG::SINAIS_VITAIS_SATURACAO;
        if ($colunaId) return self::getColunaId($tabela, $colunaId);
        return self::listarColunasTabela($tabela, 1);
    }

    public static function motivoCancelamento($colunaId = null)
    {
        $tabela = RTG::MOTIVO_CANCELAMENTO;
        if ($colunaId) return self::getColunaId($tabela, $colunaId);
        return self::listarColunasTabela($tabela, 1);
    }

    public static function situacaoChamado($colunaId = null)
    {
        $tabela = RTG::SITUACAO_CHAMADO;
        if ($colunaId) return self::getColunaId($tabela, $colunaId);
        return self::listarColunasTabela($tabela, 1);
    }
}