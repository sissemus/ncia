<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chamado extends Model
{
    protected $table = "CHAMADO";
    protected $primaryKey = "CHAMADO_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        "PACIENTE_ID",
        "TG_CHAMADO_ID",
        "TG_PRIORIDADE_ID",
        "CHAMADO_DATA",
        "CHAMADO_OBSERVACAO",
        "UNIDADE_ID_SOLICITANTE",
        "UNIDADE_ID_DESTINO",
        "CHAMADO_HORARIO_ATENDIMENTO",
        "CHAMADO_SETOR_SOLICITANTE",
        "CHAMADO_LEITO_SOLICITANTE",
        "CHAMADO_SETOR_DESTINO",
        "CHAMADO_LEITO_DESTINO",
        "CHAMADO_DISPOSITIVOS",
        "CHAMADO_PESO",
        "TG_TIPO_PRECAUCAO_ID",
        "TG_SUPORTE_O2_ID",
        "TG_SUPORTE_HEMODINAMICO_ID",
        "TG_TEMPERATURA_ID",
        "TG_FREQUENCIA_CARDIACA_ID",
        "TG_PRESSAO_ARTERIAL_ID",
        "TG_SATURACAO_ID",
        "CHAMADO_PROFISSIONAL_SOLICITANTE",
        "CHAMADO_AMBULANCIA_EXTRA",
    ];

    protected $casts = [
        "CHAMADO_ID" => "integer",
        "PACIENTE_ID" => "integer",
        "TG_CHAMADO_ID" => "integer",
        "TG_PRIORIDADE_ID" => "integer",
        "UNIDADE_ID_SOLICITANTE" => "integer",
        "UNIDADE_ID_DESTINO" => "integer",
        "CHAMADO_DATA" => "datetime",
        "CHAMADO_PESO" => "decimal:2",
        "TG_TIPO_PRECAUCAO_ID" => "integer",
        "TG_SUPORTE_O2_ID" => "integer",
        "TG_SUPORTE_HEMODINAMICO_ID" => "integer",
        "TG_TEMPERATURA_ID" => "integer",
        "TG_FREQUENCIA_CARDIACA_ID" => "integer",
        "TG_PRESSAO_ARTERIAL_ID" => "integer",
        "TG_SATURACAO_ID" => "integer",
        "CHAMADO_PROFISSIONAL_SOLICITANTE" => "string",
        "CHAMADO_AMBULANCIA_EXTRA" => "boolean",
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, "PACIENTE_ID", "PACIENTE_ID");
    }

    public function unidadeSolicitante()
    {
        return $this->belongsTo(Unidade::class, "UNIDADE_ID_SOLICITANTE", "UNIDADE_ID");
    }

    public function unidadeDestino()
    {
        return $this->belongsTo(Unidade::class, "UNIDADE_ID_DESTINO", "UNIDADE_ID");
    }

    public function procedimentos()
    {
        return $this->belongsToMany(Procedimento::class, "CHAMADO_PROCEDIMENTO", "CHAMADO_ID", "PROCEDIMENTO_ID");
    }

    public function diagnosticos()
    {
        return $this->belongsToMany(Diagnostico::class, "CHAMADO_DIAGNOSTICO", "CHAMADO_ID", "DIAGNOSTICO_ID");
    }

    public function situacoes()
    {
        return $this->hasMany(ChamadoSituacao::class, "CHAMADO_ID", "CHAMADO_ID");
    }

    public function chamadoProcedimentos()
    {
        return $this->hasMany(ChamadoProcedimento::class, "CHAMADO_ID", "CHAMADO_ID");
    }

    public function chamadoDiagnosticos()
    {
        return $this->hasMany(ChamadoDiagnostico::class, "CHAMADO_ID", "CHAMADO_ID");
    }

    public function situacaoAtual()
    {
        return $this->hasOne(ChamadoSituacao::class, "CHAMADO_ID", "CHAMADO_ID")
            ->orderByDesc("CHAMADO_SITUACAO_DATA")
            ->orderByDesc("CHAMADO_SITUACAO_ID");
    }

    public static function pesquisarParaAnalise($requisicao, $unidadeIds = null)
    {
        $query = self::with([
            'paciente',
            'unidadeSolicitante',
            'unidadeDestino',
            'situacaoAtual'
        ])
        ->join('CHAMADO_SITUACAO as cs', 'CHAMADO.CHAMADO_ID', '=', 'cs.CHAMADO_ID')
        ->whereNotExists(function ($subquery) {
            $subquery->select(\Illuminate\Support\Facades\DB::raw(1))
                ->from('CHAMADO_SITUACAO as cs2')
                ->whereColumn('cs2.CHAMADO_ID', 'cs.CHAMADO_ID')
                ->where(function ($query) {
                    $query->whereColumn('cs2.CHAMADO_SITUACAO_DATA', '>', 'cs.CHAMADO_SITUACAO_DATA')
                        ->orWhere(function ($query) {
                            $query->whereColumn('cs2.CHAMADO_SITUACAO_DATA', '=', 'cs.CHAMADO_SITUACAO_DATA')
                                ->whereColumn('cs2.CHAMADO_SITUACAO_ID', '>', 'cs.CHAMADO_SITUACAO_ID');
                        });
                });
        })
        ->select('CHAMADO.*', 'cs.TG_SITUACAO_ID');

        if ($unidadeIds !== null) {
            $query->whereIn('CHAMADO.UNIDADE_ID_SOLICITANTE', $unidadeIds);
        }

        if ($requisicao->PACIENTE_NOME) {
            $query->whereHas('paciente', function ($q) use ($requisicao) {
                $q->where('PACIENTE_NOME', 'like', '%' . $requisicao->PACIENTE_NOME . '%');
            });
        }

        if ($requisicao->CHAMADO_ID) {
            $query->where('CHAMADO.CHAMADO_ID', $requisicao->CHAMADO_ID);
        }

        if ($requisicao->TG_SITUACAO_ID) {
            $query->where('cs.TG_SITUACAO_ID', $requisicao->TG_SITUACAO_ID);
        } elseif ($requisicao->analise) {
            $query->whereIn('cs.TG_SITUACAO_ID', [1, 2, 3]);
        }

        if ($requisicao->CHAMADO_DATA) {
            $query->whereDate('CHAMADO.CHAMADO_DATA', $requisicao->CHAMADO_DATA);
        }

        if ($requisicao->TG_PRIORIDADE_ID) {
            $query->where('CHAMADO.TG_PRIORIDADE_ID', $requisicao->TG_PRIORIDADE_ID);
        }

        $query->orderBy('cs.TG_SITUACAO_ID')
              ->orderBy('CHAMADO.TG_PRIORIDADE_ID')
              ->orderByDesc('CHAMADO.CHAMADO_DATA');

        return $query->paginate();
    }

    public static function pesquisarAcompanhamento($requisicao, $unidadeIds)
    {
        return self::pesquisarParaAnalise($requisicao, $unidadeIds);
    }
}
