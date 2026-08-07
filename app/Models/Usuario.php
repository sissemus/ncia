<?php

namespace App\Models;

use App\Casts\Cpf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = "USUARIO";
    protected $primaryKey = "USUARIO_ID";
    public $timestamps = false;
    public static $snakeAttributes = false;

    protected $fillable = [
        "USUARIO_NOME",
        "USUARIO_LOGIN",
        "USUARIO_SENHA",
        "USUARIO_ATIVO",
        "USUARIO_VIGENCIA",
        "USUARIO_ULTIMO_ACESSO",
        "TG_DOCUMENTO_ID",
        "USUARIO_DOC",
        "USUARIO_EMAIL",
    ];

    protected $casts = [
        "USUARIO_ID" => "integer",
        "USUARIO_NOME" => "string",
        "USUARIO_LOGIN" => "string",
        "USUARIO_SENHA" => "string",
        "USUARIO_ATIVO" => "integer",
        "USUARIO_VIGENCIA" => "datetime",
        "USUARIO_ULTIMO_ACESSO" => "datetime",
        "TG_DOCUMENTO_ID" => "integer",
        "USUARIO_DOC" => "string",
        "USUARIO_EMAIL" => "string",
    ];

    protected $hidden = [
        'remember_token',
        'USUARIO_SENHA'
    ];

    public function getAuthPassword()
    {
        return $this->USUARIO_SENHA;
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes["USUARIO_SENHA"] = md5($value);
    }

    public function usuarioPerfis()
    {
        return $this->hasMany(UsuarioPerfil::class, "USUARIO_ID", "USUARIO_ID");
    }

    public function usuarioUnidades()
    {
        return $this->hasMany(UsuarioUnidade::class, "USUARIO_ID", "USUARIO_ID")
            ->whereHas("unidade", function ($query) {
                $query->where("UNIDADE_ATIVO", 1);
            })
            ->with("unidade");
    }

    public static function relacionamento()
    {
        return [
            "usuarioPerfis.perfil",
            "usuarioUnidades.unidade",
        ];
    }

    public static function listar($requisicao)
    {
        return self::with(self::relacionamento())
            ->when($requisicao->USUARIO_NOME, function (Builder $query) use ($requisicao) {
                return $query->where("USUARIO_NOME", "like", "%$requisicao->USUARIO_NOME%");
            })
            ->when($requisicao->USUARIO_LOGIN, function (Builder $query) use ($requisicao) {
                return $query->where("USUARIO_LOGIN", "like", "%$requisicao->USUARIO_LOGIN%");
            })
            ->when($requisicao->orderBy, function (Builder $query) use ($requisicao) {
                $requisicao->sort = $requisicao->sort ?: "asc";
                $query->orderBy($requisicao->orderBy, $requisicao->sort);
            })
            ->when(!$requisicao->orderBy, function (Builder $query) {
                $query->orderBy("USUARIO_NOME");
            });
    }

    public static function buscar($id)
    {
        return self::with(self::relacionamento())
            ->find($id);
    }

    public static function getById($userId)
    {
        return self::with(self::relacionamento())
            ->find($userId);
    }
}