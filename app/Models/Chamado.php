<?php

namespace App\Models;

use App\MyLibs\RTG;
use App\MyLibs\SituacaoChamadoEnum;
use Carbon\Carbon;
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
        "CHAMADO_TEMPERATURA",
        "CHAMADO_FREQUENCIA_CARDIACA",
        "CHAMADO_PRESSAO_ARTERIAL",
        "CHAMADO_SATURACAO_O2",
        "CHAMADO_ESCALA_GLASGOW",
        "CHAMADO_CONSELHO_PROFISSIONAL",
        "CHAMADO_NUMERO_CONSELHO",
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
        "CHAMADO_TEMPERATURA" => "string",
        "CHAMADO_FREQUENCIA_CARDIACA" => "string",
        "CHAMADO_PRESSAO_ARTERIAL" => "string",
        "CHAMADO_SATURACAO_O2" => "string",
        "CHAMADO_ESCALA_GLASGOW" => "string",
        "CHAMADO_CONSELHO_PROFISSIONAL" => "string",
        "CHAMADO_NUMERO_CONSELHO" => "string",
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

    public function atualizacoesClinicas()
    {
        return $this->hasMany(AtualizacaoClinica::class, "CHAMADO_ID", "CHAMADO_ID")
            ->orderBy("ATUALIZACAO_CLINICA_DATA")
            ->orderBy("ATUALIZACAO_CLINICA_ID");
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

    public function vinculosEquipe()
    {
        return $this->hasMany(ChamadoEquipe::class, "CHAMADO_ID", "CHAMADO_ID")
            ->orderByDesc("CHAMADO_EQUIPE_ID");
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
            $query->whereIn('cs.TG_SITUACAO_ID', [
                SituacaoChamadoEnum::ABERTO,
                SituacaoChamadoEnum::EM_ANALISE,
                SituacaoChamadoEnum::EM_ATENDIMENTO,
            ]);
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

    public static function pesquisarAcompanhamento($requisicao, $unidadeIds = null)
    {
        return self::pesquisarParaAnalise($requisicao, $unidadeIds);
    }

    public static function getDadosRelatorioChamadoEmAtendimento($id)
    {
        $chamado = self::with([
            'paciente',
            'unidadeSolicitante',
            'unidadeDestino',
            'procedimentos',
            'diagnosticos',
            'situacoes.usuario',
            'atualizacoesClinicas.usuario',
            'situacaoAtual',
            'vinculosEquipe.equipe.veiculo',
            'vinculosEquipe.equipe.equipeProfissional.profissional',
        ])->findOrFail($id);

        $genericos = TabelaGenerica::whereIn('TABELA_ID', [
            RTG::SEXO,
            RTG::PRIORIDADE_PACIENTE,
            RTG::TIPO_CHAMADO,
            RTG::TIPO_PRECAUCAO,
            RTG::SUPORTE_O2,
            RTG::SUPORTE_HEMODINAMICO,
            RTG::SITUACAO_CHAMADO,
        ])->get();

        $descricao = function ($tabelaId, $colunaId) use ($genericos) {
            $item = $genericos->first(function ($item) use ($tabelaId, $colunaId) {
                return (int) $item->TABELA_ID === (int) $tabelaId
                    && (int) $item->COLUNA_ID === (int) $colunaId;
            });

            return $item ? $item->DESCRICAO : '-';
        };

        $paciente = $chamado->paciente;
        $nomePaciente = $paciente ? trim((string) $paciente->PACIENTE_NOME) : '';
        if (!$nomePaciente && $paciente && $paciente->PACIENTE_VULNERABILIDADE_SOCIAL) {
            $nomePaciente = 'PACIENTE EM VULNERABILIDADE SOCIAL';
        }

        $profissionalSolicitante = trim((string) $chamado->CHAMADO_PROFISSIONAL_SOLICITANTE);
        if (ctype_digit($profissionalSolicitante)) {
            $profissional = Profissional::find((int) $profissionalSolicitante);
            $profissionalSolicitante = $profissional
                ? $profissional->PROFISSIONAL_NOME
                : $profissionalSolicitante;
        }

        $vinculoEquipe = $chamado->vinculosEquipe->first(function ($vinculo) {
            return (int) $vinculo->CHAMADO_EQUIPE_ATIVO === 1;
        }) ?: $chamado->vinculosEquipe->first();
        $equipe = $vinculoEquipe ? $vinculoEquipe->equipe : null;
        $veiculo = $equipe ? $equipe->veiculo : null;
        $profissionais = $equipe
            ? $equipe->equipeProfissional
                ->filter(function ($item) {
                    return (int) $item->EQUIPE_PROFISSIONAL_ATIVO === 1;
                })
                ->map(function ($item) {
                    return $item->profissional ? $item->profissional->PROFISSIONAL_NOME : null;
                })
                ->filter()
                ->implode(', ')
            : '';

        $nascimento = $paciente && $paciente->PACIENTE_DT_NASCIMENTO
            ? Carbon::parse($paciente->PACIENTE_DT_NASCIMENTO)
            : null;
        $eventos = $chamado->situacoes->map(function ($situacao) {
            return [
                'tipo' => 'situacao',
                'id' => $situacao->CHAMADO_SITUACAO_ID,
                'data' => $situacao->CHAMADO_SITUACAO_DATA,
                'item' => $situacao,
            ];
        })->concat($chamado->atualizacoesClinicas->map(function ($atualizacao) {
            return [
                'tipo' => 'atualizacao',
                'id' => $atualizacao->ATUALIZACAO_CLINICA_ID,
                'data' => $atualizacao->ATUALIZACAO_CLINICA_DATA,
                'item' => $atualizacao,
            ];
        }))->sortBy(function ($evento) {
            return sprintf(
                '%s-%s-%010d',
                Carbon::parse($evento['data'])->format('YmdHis.u'),
                $evento['tipo'],
                $evento['id']
            );
        })->values();

        if ($eventos->isEmpty()) {
            $eventos = collect([null]);
        }

        $escapar = function ($valor) {
            return htmlspecialchars(self::valorRelatorio($valor), ENT_QUOTES, 'UTF-8');
        };
        $atualizacoesClinicas = $chamado->atualizacoesClinicas->map(function ($atualizacao) use ($descricao, $escapar) {
            $usuario = $atualizacao->usuario ? $atualizacao->usuario->USUARIO_NOME : null;

            return '<b>Atualização Clínica Nº ' . $atualizacao->ATUALIZACAO_CLINICA_ID . '</b><br/>'
                . '<b>Data/hora:</b> ' . self::formatarDataHoraRelatorio($atualizacao->ATUALIZACAO_CLINICA_DATA)
                . '     <b>Registrado por:</b> ' . $escapar($usuario) . '<br/>'
                . '<b>Prioridade:</b> ' . $escapar($descricao(RTG::PRIORIDADE_PACIENTE, $atualizacao->TG_PRIORIDADE_ANTERIOR_ID))
                . ' para ' . $escapar($descricao(RTG::PRIORIDADE_PACIENTE, $atualizacao->TG_PRIORIDADE_ID)) . '<br/>'
                . '<b>Profissional responsável:</b> ' . $escapar($atualizacao->ATUALIZACAO_CLINICA_PROFISSIONAL)
                . '     <b>Conselho:</b> ' . $escapar($atualizacao->ATUALIZACAO_CLINICA_CONSELHO)
                . ' ' . $escapar($atualizacao->ATUALIZACAO_CLINICA_NUMERO_CONSELHO) . '<br/>'
                . '<b>Precaução:</b> ' . $escapar($descricao(RTG::TIPO_PRECAUCAO, $atualizacao->TG_TIPO_PRECAUCAO_ID))
                . '     <b>Suporte O2:</b> ' . $escapar($descricao(RTG::SUPORTE_O2, $atualizacao->TG_SUPORTE_O2_ID))
                . '     <b>Suporte hemodinâmico:</b> ' . $escapar($descricao(RTG::SUPORTE_HEMODINAMICO, $atualizacao->TG_SUPORTE_HEMODINAMICO_ID)) . '<br/>'
                . '<b>Temperatura:</b> ' . $escapar($atualizacao->ATUALIZACAO_CLINICA_TEMPERATURA)
                . '     <b>Pressão arterial:</b> ' . $escapar($atualizacao->ATUALIZACAO_CLINICA_PRESSAO_ARTERIAL)
                . '     <b>Frequência cardíaca:</b> ' . $escapar($atualizacao->ATUALIZACAO_CLINICA_FREQUENCIA_CARDIACA) . '<br/>'
                . '<b>Saturação O2:</b> ' . $escapar($atualizacao->ATUALIZACAO_CLINICA_SATURACAO_O2)
                . '     <b>Escala Glasgow:</b> ' . $escapar($atualizacao->ATUALIZACAO_CLINICA_ESCALA_GLASGOW) . '<br/>'
                . '<b>Justificativa médica:</b> ' . nl2br($escapar($atualizacao->ATUALIZACAO_CLINICA_JUSTIFICATIVA_MEDICA), false);
        })->implode('<br/><br/>');

        return $eventos->map(function ($evento) use (
            $chamado,
            $paciente,
            $nomePaciente,
            $nascimento,
            $profissionalSolicitante,
            $descricao,
            $equipe,
            $veiculo,
            $profissionais,
            $atualizacoesClinicas
        ) {
            $itemHistorico = $evento ? $evento['item'] : null;
            $atualizacao = $evento && $evento['tipo'] === 'atualizacao' ? $itemHistorico : null;
            $situacao = $evento && $evento['tipo'] === 'situacao' ? $itemHistorico : null;

            return [
                'CHAMADO_ID' => (string) $chamado->CHAMADO_ID,
                'SITUACAO_ATUAL' => $descricao(RTG::SITUACAO_CHAMADO, $chamado->situacaoAtual->TG_SITUACAO_ID),
                'PACIENTE_NOME' => self::valorRelatorio($nomePaciente),
                'PACIENTE_CPF' => self::valorRelatorio($paciente ? $paciente->PACIENTE_CPF : null),
                'PACIENTE_NASCIMENTO' => $nascimento ? $nascimento->format('d/m/Y') : '-',
                'PACIENTE_IDADE' => $nascimento ? $nascimento->age . ' anos' : '-',
                'PACIENTE_SEXO' => $descricao(RTG::SEXO, $paciente ? $paciente->TG_SEXO_ID : null),
                'PACIENTE_VULNERABILIDADE' => self::simNao($paciente && $paciente->PACIENTE_VULNERABILIDADE_SOCIAL),
                'PACIENTE_TEMPORARIO' => self::simNao($paciente && $paciente->PACIENTE_TEMPORARIO),
                'CHAMADO_DATA' => self::formatarDataHoraRelatorio($chamado->CHAMADO_DATA),
                'TIPO_CHAMADO' => $descricao(RTG::TIPO_CHAMADO, $chamado->TG_CHAMADO_ID),
                'PRIORIDADE' => $descricao(RTG::PRIORIDADE_PACIENTE, $chamado->TG_PRIORIDADE_ID),
                'HORARIO_ATENDIMENTO' => self::formatarHoraRelatorio($chamado->CHAMADO_HORARIO_ATENDIMENTO),
                'AMBULANCIA_EXTRA' => self::simNao($chamado->CHAMADO_AMBULANCIA_EXTRA),
                'UNIDADE_ORIGEM' => self::valorRelatorio($chamado->unidadeSolicitante ? $chamado->unidadeSolicitante->UNIDADE_NOME : null),
                'SETOR_ORIGEM' => self::valorRelatorio($chamado->CHAMADO_SETOR_SOLICITANTE),
                'LEITO_ORIGEM' => self::valorRelatorio($chamado->CHAMADO_LEITO_SOLICITANTE),
                'UNIDADE_DESTINO' => self::valorRelatorio($chamado->unidadeDestino ? $chamado->unidadeDestino->UNIDADE_NOME : null),
                'SETOR_DESTINO' => self::valorRelatorio($chamado->CHAMADO_SETOR_DESTINO),
                'LEITO_DESTINO' => self::valorRelatorio($chamado->CHAMADO_LEITO_DESTINO),
                'PROFISSIONAL_SOLICITANTE' => self::valorRelatorio($profissionalSolicitante),
                'CONSELHO_PROFISSIONAL' => self::valorRelatorio($chamado->CHAMADO_CONSELHO_PROFISSIONAL),
                'NUMERO_CONSELHO' => self::valorRelatorio($chamado->CHAMADO_NUMERO_CONSELHO),
                'PROCEDIMENTOS' => self::valorRelatorio($chamado->procedimentos->pluck('PROCEDIMENTO_DESCRICAO')->implode(', ')),
                'DIAGNOSTICOS' => self::valorRelatorio($chamado->diagnosticos->pluck('DIAGNOSTICO_DESCRICAO')->implode(', ')),
                'DISPOSITIVOS' => self::valorRelatorio($chamado->CHAMADO_DISPOSITIVOS),
                'PESO' => $chamado->CHAMADO_PESO !== null ? $chamado->CHAMADO_PESO . ' kg' : '-',
                'PRECAUCAO' => $descricao(RTG::TIPO_PRECAUCAO, $chamado->TG_TIPO_PRECAUCAO_ID),
                'SUPORTE_O2' => $descricao(RTG::SUPORTE_O2, $chamado->TG_SUPORTE_O2_ID),
                'SUPORTE_HEMODINAMICO' => $descricao(RTG::SUPORTE_HEMODINAMICO, $chamado->TG_SUPORTE_HEMODINAMICO_ID),
                'TEMPERATURA' => self::valorRelatorio($chamado->CHAMADO_TEMPERATURA),
                'PRESSAO_ARTERIAL' => self::valorRelatorio($chamado->CHAMADO_PRESSAO_ARTERIAL),
                'FREQUENCIA_CARDIACA' => self::valorRelatorio($chamado->CHAMADO_FREQUENCIA_CARDIACA),
                'SATURACAO_O2' => self::valorRelatorio($chamado->CHAMADO_SATURACAO_O2),
                'ESCALA_GLASGOW' => self::valorRelatorio($chamado->CHAMADO_ESCALA_GLASGOW),
                'OBSERVACOES' => self::valorRelatorio($chamado->CHAMADO_OBSERVACAO),
                'VEICULO' => self::valorRelatorio($veiculo ? $veiculo->VEICULO_IDENTIFICACAO : null),
                'VEICULO_PLACA' => self::valorRelatorio($veiculo ? $veiculo->VEICULO_PLACA : null),
                'EQUIPE' => $equipe ? 'Equipe Nº ' . $equipe->EQUIPE_ID . ($equipe->EQUIPE_TURNO ? ' - ' . $equipe->EQUIPE_TURNO : '') : '-',
                'PROFISSIONAIS' => self::valorRelatorio($profissionais),
                'ATUALIZACOES_CLINICAS' => self::valorRelatorio($atualizacoesClinicas),
                'HISTORICO_SITUACAO' => $atualizacao
                    ? 'ATUALIZAÇÃO CLÍNICA Nº ' . $atualizacao->ATUALIZACAO_CLINICA_ID
                    : ($situacao ? $descricao(RTG::SITUACAO_CHAMADO, $situacao->TG_SITUACAO_ID) : '-'),
                'HISTORICO_DATA' => $evento
                    ? self::formatarDataHoraRelatorio($evento['data'])
                    : '-',
                'HISTORICO_USUARIO' => self::valorRelatorio($itemHistorico && $itemHistorico->usuario
                    ? $itemHistorico->usuario->USUARIO_NOME
                    : null),
                'HISTORICO_OBSERVACAO' => self::valorRelatorio($atualizacao
                    ? $atualizacao->ATUALIZACAO_CLINICA_JUSTIFICATIVA_MEDICA
                    : ($situacao ? $situacao->CHAMADO_SITUACAO_OBSERVACAO : null)),
            ];
        })->all();
    }

    private static function valorRelatorio($valor)
    {
        $valor = trim((string) $valor);
        return $valor !== '' ? $valor : '-';
    }

    private static function simNao($valor)
    {
        return $valor ? 'Sim' : 'Não';
    }

    private static function formatarDataHoraRelatorio($valor)
    {
        return $valor ? Carbon::parse($valor)->format('d/m/Y H:i:s') : '-';
    }

    private static function formatarHoraRelatorio($valor)
    {
        if (!$valor) {
            return '-';
        }

        $valor = (string) $valor;
        return preg_match('/(\d{2}:\d{2})/', $valor, $resultado) ? $resultado[1] : $valor;
    }
}
