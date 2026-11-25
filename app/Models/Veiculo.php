<?php

namespace App\Models;

use App\MyLibs\RTG;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Veiculo extends Model
{
    protected $table = "VEICULO";
    protected $primaryKey = "VEICULO_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        "VEICULO_IDENTIFICACAO",
        "VEICULO_PLACA",
        "TG_TIPO_VEICULO_ID",
        "TG_SITUACAO_VEICULO_ID",
        "VEICULO_ATIVO",
    ];

    protected $casts = [
        "VEICULO_ID" => "integer",
        "VEICULO_IDENTIFICACAO" => "string",
        "VEICULO_PLACA" => "string",
        "TG_TIPO_VEICULO_ID" => "integer",
        "TG_SITUACAO_VEICULO_ID" => "integer",
        "VEICULO_ATIVO" => "integer",
    ];

    public function tipoVeiculo()
    {
        return $this->belongsTo(TabelaGenerica::class, "TG_TIPO_VEICULO_ID", "COLUNA_ID")
            ->where('TABELA_ID', RTG::TIPO_VEICULO);
    }

    public function situacaoVeiculo()
    {
        return $this->belongsTo(TabelaGenerica::class, "TG_SITUACAO_VEICULO_ID", "COLUNA_ID")
            ->where('TABELA_ID', RTG::SITUACAO_VEICULO);
    }

    public function equipe()
    {
        //MUDANDO PARA EQUIPES ATIVAS
        return $this->hasOne(Equipe::class, 'VEICULO_ID', 'VEICULO_ID')
            ->where('EQUIPE_ATIVO', '=', 1);
        // ->where('EQUIPE_DATA', now()->format('Y-m-d')
        
    }

    public function equipes()
    {

        return $this->hasMany(Equipe::class, 'VEICULO_ID', 'VEICULO_ID')
            ->whereBetween('EQUIPE_DATA_FIM', [
                 now()->startOfDay(),
                 now()->addDay()->endOfDay(),
            ])
            ->where('EQUIPE_ATIVO', '=', 1);
    }

    public function equipesAtivas()
    {
        return $this->hasMany(Equipe::class, 'VEICULO_ID', 'VEICULO_ID')
            ->where('EQUIPE_ATIVO', 1);
    }

    public function vinculoAtivo()
    {
        return $this->hasOne(VeiculoUnidade::class, "VEICULO_ID", "VEICULO_ID")
            ->whereNull("VEICULO_UNIDADE_DT_FIM");
    }

    public static function relacionamento()
    {
        return [
            "tipoVeiculo",
            "vinculoAtivo.unidade",
            "situacaoVeiculo",
            "equipes",
            "equipes.equipeProfissional",
            "equipes.equipeProfissional.profissional",
            "equipes.equipeProfissional.profissional.tipoProfissional"
        ];
    }

    public static function pesquisar($requisicao)
    {
        
        return self::with(self::relacionamento())
            ->when($requisicao->VEICULO_IDENTIFICACAO, function (Builder $query) use ($requisicao) {
                return $query->where("VEICULO_IDENTIFICACAO", "like", "%" . $requisicao->VEICULO_IDENTIFICACAO . "%");
            })
            ->when($requisicao->TG_TIPO_VEICULO_ID, function (Builder $query) use ($requisicao) {
                return $query->where("TG_TIPO_VEICULO_ID", $requisicao->TG_TIPO_VEICULO_ID);
            })
            ->when($requisicao->TG_SITUACAO_VEICULO_ID, function (Builder $query) use ($requisicao) {
                return $query->where("TG_SITUACAO_VEICULO_ID", $requisicao->TG_SITUACAO_VEICULO_ID);
            })
            ->when($requisicao->VEICULO_ATIVO !== null && $requisicao->VEICULO_ATIVO !== '', function (Builder $query) use ($requisicao) {
                return $query->where("VEICULO_ATIVO", $requisicao->VEICULO_ATIVO);
            })
            ->orderBy("VEICULO_IDENTIFICACAO")
            ->paginate();
    }

    // diferença é que relacionamento usa equipe e relacionamentoCadastrar usa equipes
    public static function relacionamentoCadastrar()
    {

        return [
            "tipoVeiculo",
            "situacaoVeiculo",
            "equipe",
            'equipesAtivas'
                => function($q) {
                    self::filtrarEquipesVisiveis($q);
                    $q->orderBy('EQUIPE_DATA');
                }
            //  => function ($query) use ($hoje) {
            //     $query->where(function ($query) use ($hoje) {
            //         $query->whereDate('EQUIPE_DATA', $hoje->toDateString())
            //             ->orWhere(function ($query) use ($hoje) {
            //                 // Manter a SN anterior acessível para entregar sua fila à SD.
            //                 $query->whereDate('EQUIPE_DATA', $hoje->copy()->subDay()->toDateString())
            //                     ->where('EQUIPE_TURNO', 'SN')
            //                     ->whereExists(function ($query) {
            //                         $query->selectRaw('1')->from('CHAMADO_EQUIPE as ce_plantao')
            //                             ->whereColumn('ce_plantao.EQUIPE_ID', 'EQUIPE.EQUIPE_ID')
            //                             ->where('ce_plantao.CHAMADO_EQUIPE_ATIVO', 1);
            //                     });
            //             });
            //     })->orderBy('EQUIPE_DATA')->orderBy('EQUIPE_ID');
            // },
            // Somente integrantes ativos: removidos na edição saem da grade (exclusão lógica).
            , 'equipesAtivas.equipeProfissional' => function ($q) {
                $q->where('EQUIPE_PROFISSIONAL_ATIVO', 1);
            },
            'equipesAtivas.equipeProfissional.profissional',
            'equipesAtivas.equipeProfissional.profissional.tipoProfissional',
            "vinculoAtivo",
            "vinculoAtivo.unidade",
        ];
    }

    /**
     * Equipes exibidas no Cadastro de Equipes: ativas com plantão em vigor OU
     * seguras por chamado em fila/em atendimento (precisam continuar visíveis
     * para "Trocar plantão" e, depois, a lixeira).
     */
    public static function filtrarEquipesVisiveis($query)
    {
        $agora = Carbon::now()->format('Y-m-d H:i');

        return $query->where('EQUIPE_ATIVO', '=', 1)
            ->where(function ($w) use ($agora) {
                $w->where('EQUIPE_DATA_FIM', '>=', $agora)
                    ->orWhere(function ($p) {
                        $p->comChamadoPendente();
                    });
            });
    }

    /**
     * Filtros de equipe da tela Cadastro de Equipes (Nº, turno, profissional, situação).
     */
    private static function filtrarEquipesPesquisa($query, array $filtros)
    {
        $agora = Carbon::now()->format('Y-m-d H:i');

        return $query
            ->when($filtros['EQUIPE_ID'], function ($q, $id) {
                $q->where('EQUIPE_ID', $id);
            })
            ->when($filtros['EQUIPE_TURNO'], function ($q, $turno) {
                $q->where('EQUIPE_TURNO', $turno);
            })
            ->when($filtros['PROFISSIONAL_NOME'], function ($q, $nome) {
                $q->whereHas('equipeProfissional', function ($ep) use ($nome) {
                    $ep->where('EQUIPE_PROFISSIONAL_ATIVO', 1)
                        ->whereHas('profissional', function ($p) use ($nome) {
                            $p->where('PROFISSIONAL_NOME', 'like', '%' . $nome . '%');
                        });
                });
            })
            ->when($filtros['SITUACAO_PLANTAO'] === 'VIGENTE', function ($q) use ($agora) {
                $q->where('EQUIPE_DATA_FIM', '>=', $agora);
            })
            ->when($filtros['SITUACAO_PLANTAO'] === 'PENDENTE', function ($q) use ($agora) {
                $q->where('EQUIPE_DATA_FIM', '<', $agora);
            });
    }

    public static function pesquisarCadastrar($requisicao)
    {
        $filtros = [
            'EQUIPE_ID' => (int) $requisicao->EQUIPE_ID ?: null,
            'EQUIPE_TURNO' => in_array($requisicao->EQUIPE_TURNO, ['SD', 'SN'], true) ? $requisicao->EQUIPE_TURNO : null,
            'PROFISSIONAL_NOME' => trim((string) $requisicao->PROFISSIONAL_NOME) ?: null,
            'SITUACAO_PLANTAO' => in_array($requisicao->SITUACAO_PLANTAO, ['VIGENTE', 'PENDENTE'], true) ? $requisicao->SITUACAO_PLANTAO : null,
        ];
        $temFiltroEquipe = (bool) array_filter($filtros);

        // IDs das equipes que atendem aos filtros (para limitar as equipes exibidas de cada veículo)
        $idsFiltrados = null;
        if ($temFiltroEquipe) {
            $idsFiltrados = self::filtrarEquipesPesquisa(self::filtrarEquipesVisiveis(Equipe::query()), $filtros)
                ->pluck('EQUIPE_ID')->all();
        }

        $veiculos = self::with(self::relacionamentoCadastrar())
            // Com filtro de equipe (ou "somente com equipe"), só veículos com equipe que atenda
            ->when($temFiltroEquipe || $requisicao->SOMENTE_COM_EQUIPE, function (Builder $query) use ($idsFiltrados) {
                return $query->whereHas('equipesAtivas', function ($q) use ($idsFiltrados) {
                    self::filtrarEquipesVisiveis($q);
                    if ($idsFiltrados !== null) {
                        $q->whereIn('EQUIPE_ID', $idsFiltrados ?: [0]);
                    }
                });
            })
            ->when($requisicao->VEICULO_IDENTIFICACAO, function (Builder $query) use ($requisicao) {
                return $query->where("VEICULO_IDENTIFICACAO", "like", "%" . $requisicao->VEICULO_IDENTIFICACAO . "%");
            })
            ->when($requisicao->TG_TIPO_VEICULO_ID, function (Builder $query) use ($requisicao) {
                return $query->where("TG_TIPO_VEICULO_ID", $requisicao->TG_TIPO_VEICULO_ID);
            })
            ->when($requisicao->TG_SITUACAO_VEICULO_ID, function (Builder $query) use ($requisicao) {
                return $query->where("TG_SITUACAO_VEICULO_ID", $requisicao->TG_SITUACAO_VEICULO_ID);
            })
            ->when($requisicao->VEICULO_ATIVO !== null && $requisicao->VEICULO_ATIVO !== '', function (Builder $query) use ($requisicao) {
                return $query->where("VEICULO_ATIVO", $requisicao->VEICULO_ATIVO);
            })
            ->orderBy("VEICULO_IDENTIFICACAO")
            ->paginate();

        $veiculos->getCollection()->each(function ($veiculo) use ($idsFiltrados) {
            $equipes = $veiculo->getRelation('equipesAtivas');
            if ($idsFiltrados !== null) {
                $equipes = $equipes->whereIn('EQUIPE_ID', $idsFiltrados)->values();
            }
            $veiculo->setRelation('equipes', $equipes);
            $veiculo->unsetRelation('equipesAtivas');
        });

        return $veiculos;
    }

    public static function buscar($id)
    {
        return self::with(self::relacionamento())->findOrFail($id);
    }
}
