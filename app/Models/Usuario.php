<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property integer USUARIO_ID
 * @property string USUARIO_CPF
 * @property string USUARIO_NOME
 * @property string USARIO_LOGIN
 * @property string USUARIO_SENHA
 * @property integer USUARIO_ATIVO
 * @method static Usuario find(array|string|null $post)
 */
class Usuario extends Authenticatable {
    use HasFactory, Notifiable;

    protected $table = "USUARIO";
    protected $primaryKey = "USUARIO_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;
    protected $fillable = [
        "USUARIO_CPF",
        "USUARIO_NOME",
        "USUARIO_LOGIN",
        "USUARIO_SENHA",
        "USUARIO_ATIVO",
        "USUARIO_ADM",
    ];
    protected $casts = [
        "USUARIO_ID" => "integer",
        "USUARIO_ATIVO" => "integer",
        "USUARIO_ADM" => "integer",
    ];
    protected $hidden = [
        'remember_token', 'USUARIO_SENHA'
    ];

    public static $relacionamentos = [
        "usuarioLocais.local.localSituacoes",
        "usuarioLocais.local.publicos",
        "usuarioLocais.local.vacinaLocais.vacina",
    ];

    public function usuarioLocais() {
        return $this->hasMany(UsuarioLocal::class, "USUARIO_ID", "USUARIO_ID");
    }

    public function getAuthPassword() {
        return $this->USUARIO_SENHA;
    }

    public function setPasswordAttribute($value) {
//        $this->attributes["USUARIO_SENHA"] = md5($value);
        $this->attributes["USUARIO_SENHA"] = $value;
    }

    public static function listar() {
        return self::with([])
            ->orderBy('USUARIO_LOGIN')
            ->paginate();
    }

    public static function listAll() {
        return self::with(self::$relacionamentos)
            ->orderBy('USUARIO_LOGIN')
            ->paginate();
    }

    public static function pesquisar($requisicao) {
        return self::with([])
            ->when($requisicao->USUARIO_LOGIN, function (Builder $query) use ($requisicao) {
                return $query->where('USUARIO_LOGIN', 'like', "%" . $requisicao->USUARIO_LOGIN . "%");
            })
            ->when($requisicao->USUARIO_NOME, function (Builder $query) use ($requisicao) {
                return $query->where('USUARIO_NOME', 'like', "%" . $requisicao->USUARIO_NOME . "%");
            })
            ->when($requisicao->USUARIO_CPF, function (Builder $query) use ($requisicao) {
                return $query->where('USUARIO_CPF', '=', $requisicao->USUARIO_CPF);
            })
            ->when($requisicao->USUARIO_EMAIL, function (Builder $query) use ($requisicao) {
                return $query->where('USUARIO_EMAIL', 'like', "%" . $requisicao->USUARIO_EMAIL . "%");
            })
            ->when($requisicao->USUARIO_TIPO_UNIDADE, function (Builder $query) use ($requisicao) {
                return $query->where('USUARIO_TIPO_UNIDADE', '=', $requisicao->USUARIO_TIPO_UNIDADE);
            })
            ->when($requisicao->FUNCIONARIO_PESSOA, function (Builder $query) use ($requisicao) {
                $query->whereHas('funcionario', function (Builder $query) use ($requisicao) {
                    return $query->where('FUNCIONARIO_MATRICULA', 'like', "%" . $requisicao->FUNCIONARIO_PESSOA . "%")
                        ->orWhereHas('pessoa', function (Builder $query) use ($requisicao) {
                            $query->where('PESSOA_NOME', 'LIKE', "%" . $requisicao->FUNCIONARIO_PESSOA . "%");
                        });
                });
            })
            ->orderBy('USUARIO_LOGIN')
            ->get();
    }

    public static function buscar($id) {
        return self::with([])
            ->find($id);
    }

    public static function getById($userId) {
        return self::with(self::$relacionamentos)->find($userId);
    }

    public static function getUserMenu(): array {
        if (auth()->user()->USUARIO_ADM == 1) {
            return self::getDevMenu();
        }
        else {
            return self::getMenuUser();
        }
    }

    private static function getDevMenu(): array {
        return [
            [
                'icon' => 'mdi-home',
                'text' => 'Home',
                'icon-alt' => 'mdi-home',
                'model' => false,
                'path' => url('/home'),
            ],
            [
                'icon' => 'mdi-map-marker',
                'text' => 'Locais',
                'icon-alt' => 'mdi-map-marker',
                'model' => false,
                'path' => url('/local/view'),
            ],
            [
                'icon' => 'mdi-iv-bag',
                'text' => 'Doses',
                'icon-alt' => 'mdi-iv-bag',
                'model' => false,
                'path' => url('/dose/view'),
            ],
            [
                'icon' => 'mdi-needle',
                'text' => 'Vacinas',
                'icon-alt' => 'mdi-needle',
                'model' => false,
                'path' => url('/vacina/view'),
            ],
            [
                'icon' => 'mdi-needle',
                'text' => 'Entradas',
                'icon-alt' => 'mdi-needle',
                'model' => false,
                'path' => url('/vacina-local/view'),
            ],
            [
                'icon' => 'mdi-needle',
                'text' => 'Vacinação',
                'icon-alt' => 'mdi-needle',
                'model' => false,
                'path' => url('/vacinacao/view'),
            ],
            [
                'icon' => 'mdi-account-circle',
                'text' => 'Usuários',
                'icon-alt' => 'mdi-account-circle',
                'model' => false,
                'path' => url('/usuario/view'),
            ],
        ];
    }

    private static function getMenuUser(): array {
        return [
            [
                'icon' => 'mdi-home',
                'text' => 'Home',
                'icon-alt' => 'mdi-home',
                'model' => false,
                'path' => url('/home'),
            ]
        ];
    }
}
