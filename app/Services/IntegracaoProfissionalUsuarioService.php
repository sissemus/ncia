<?php

namespace App\Services;

use App\Models\Perfil;
use App\Models\Profissional;
use App\Models\Usuario;
use App\Models\UsuarioPerfil;
use App\MyLibs\PerfilEnum;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class IntegracaoProfissionalUsuarioService
{
    public function podeGerenciarAcesso(Usuario $operador): bool
    {
        return $operador->usuarioPerfis()->where('USUARIO_PERFIL_ATIVO', 1)
            ->whereHas('perfil', function ($query) {
                $query->where('PERFIL_ATIVO', 1);
            })->whereIn('PERFIL_ID', [PerfilEnum::DESENVOLVEDOR, PerfilEnum::ADMINISTRADOR])->exists();
    }

    public function perfisPermitidos(Usuario $operador)
    {
        $query = Perfil::where('PERFIL_ATIVO', 1);
        if (!$this->podeGerenciarAcesso($operador)) {
            return $query->whereRaw('1 = 0');
        }
        $desenvolvedor = $operador->usuarioPerfis()->where('USUARIO_PERFIL_ATIVO', 1)
            ->where('PERFIL_ID', PerfilEnum::DESENVOLVEDOR)
            ->whereHas('perfil', function ($query) { $query->where('PERFIL_ATIVO', 1); })->exists();
        return $query->when(!$desenvolvedor, function ($query) {
            $query->where('PERFIL_ID', '<>', PerfilEnum::DESENVOLVEDOR);
        });
    }

    public function usuarioPorCpf(string $cpf, bool $bloquear = false): ?Usuario
    {
        $query = Usuario::whereRaw("REPLACE(REPLACE(REPLACE(USUARIO_CPF, '.', ''), '-', ''), ' ', '') = ?", [$cpf]);
        $usuarios = ($bloquear ? $query->lockForUpdate() : $query)->get();
        if ($usuarios->count() > 1) {
            throw ValidationException::withMessages(['PROFISSIONAL_CPF' => 'Há mais de um usuário com este CPF. Regularize os cadastros antes de vincular.']);
        }
        return $usuarios->first();
    }

    public function gerarLogin(string $nome): string
    {
        $partes = preg_split('/\s+/u', trim($nome), -1, PREG_SPLIT_NO_EMPTY);
        $partes = array_values(array_filter(array_map(function ($parte) {
            return preg_replace('/[^a-z0-9]/', '', strtolower(Str::ascii($parte)));
        }, $partes)));
        if (!$partes) {
            throw ValidationException::withMessages(['PROFISSIONAL_NOME' => 'Informe um nome que permita gerar o login.']);
        }
        $base = $partes[0] . (count($partes) > 1 ? '.' . end($partes) : '');
        for ($numero = 1; ; $numero++) {
            $sufixo = $numero === 1 ? '' : (string) $numero;
            $login = rtrim(substr($base, 0, 50 - strlen($sufixo)), '.') . $sufixo;
            if (!Usuario::whereRaw('LOWER(USUARIO_LOGIN) = ?', [$login])->exists()) {
                return $login;
            }
        }
    }

    public function salvarProfissional(array $dados, Usuario $operador): array
    {
        // Retry the whole transaction: SQL Server aborts a transaction after a unique-key violation.
        for ($tentativa = 0; $tentativa < 5; $tentativa++) {
            try {
                return DB::transaction(function () use ($dados, $operador) {
                    $profissional = empty($dados['PROFISSIONAL_ID']) ? new Profissional()
                        : Profissional::whereKey($dados['PROFISSIONAL_ID'])->lockForUpdate()->firstOrFail();
                    $profissional->fill($dados);
                    $usuario = $profissional->USUARIO_ID
                        ? Usuario::whereKey($profissional->USUARIO_ID)->lockForUpdate()->firstOrFail() : null;
                    $criado = false;
                    if (!$usuario && !empty($dados['CRIAR_ACESSO'])) {
                        abort_unless($this->podeGerenciarAcesso($operador), 403);
                        $usuario = $this->usuarioPorCpf(preg_replace('/\D/', '', $profissional->PROFISSIONAL_CPF), true);
                        if ($usuario) {
                            if ((int) ($dados['VINCULAR_USUARIO_ID'] ?? 0) !== $usuario->USUARIO_ID) {
                                throw ValidationException::withMessages(['VINCULAR_USUARIO_ID' => 'Confirme o vínculo com o usuário já cadastrado para este CPF.']);
                            }
                            if (Profissional::where('USUARIO_ID', $usuario->USUARIO_ID)->exists()) {
                                throw ValidationException::withMessages(['VINCULAR_USUARIO_ID' => 'Este usuário já está vinculado a outro profissional.']);
                            }
                        } else {
                            if (!empty($dados['VINCULAR_USUARIO_ID'])) {
                                throw ValidationException::withMessages(['VINCULAR_USUARIO_ID' => 'O CPF foi alterado. Consulte e confirme o usuário novamente.']);
                            }
                            $perfil = $this->perfisPermitidos($operador)->whereKey($dados['PERFIL_ID'] ?? null)->first();
                            if (!$perfil) {
                                throw ValidationException::withMessages(['PERFIL_ID' => 'Selecione um perfil ativo que você pode conceder.']);
                            }
                            $usuario = new Usuario();
                            $usuario->USUARIO_LOGIN = $this->gerarLogin($profissional->PROFISSIONAL_NOME);
                            $usuario->USUARIO_SENHA = md5('123');
                            $usuario->USUARIO_ATIVO = 1;
                            $criado = true;
                        }
                    }
                    if ($usuario) {
                        $this->validarUsuario($profissional, $usuario);
                        $usuario->USUARIO_NOME = $profissional->PROFISSIONAL_NOME;
                        $usuario->USUARIO_CPF = $profissional->PROFISSIONAL_CPF;
                        $usuario->USUARIO_CONTATO = $profissional->PROFISSIONAL_WHATSAPP;
                        $usuario->save();
                        $profissional->USUARIO_ID = $usuario->USUARIO_ID;
                        if ($criado) {
                            UsuarioPerfil::create(['USUARIO_ID' => $usuario->USUARIO_ID,
                                'PERFIL_ID' => $perfil->PERFIL_ID, 'USUARIO_PERFIL_ATIVO' => 1]);
                        }
                    }
                    $profissional->save();
                    return ['profissional' => Profissional::buscar($profissional->PROFISSIONAL_ID), 'usuario_criado' => $criado];
                }, 3);
            } catch (QueryException $e) {
                if (!$this->conflitoLogin($e)) {
                    throw $e;
                }
            }
        }
        throw ValidationException::withMessages(['CRIAR_ACESSO' => 'Não foi possível reservar o login. Tente salvar novamente.']);
    }

    private function conflitoLogin(QueryException $e): bool
    {
        $mensagem = strtolower($e->getMessage());
        return in_array((string) $e->getCode(), ['23000', '23505'], true)
            && (strpos($mensagem, 'usuario_login') !== false || strpos($mensagem, 'ux_usuario_login_integracao') !== false);
    }

    private function validarUsuario(Profissional $profissional, Usuario $usuario): void
    {
        $existente = $this->usuarioPorCpf(preg_replace('/\D/', '', $profissional->PROFISSIONAL_CPF), true);
        if ($existente && $existente->getKey() !== $usuario->getKey()) {
            throw ValidationException::withMessages(['PROFISSIONAL_CPF' => 'Este CPF já pertence a outro usuário.']);
        }
        Validator::make([
            'USUARIO_NOME' => $profissional->PROFISSIONAL_NOME,
            'USUARIO_CPF' => preg_replace('/\D/', '', $profissional->PROFISSIONAL_CPF),
            'USUARIO_CONTATO' => preg_replace('/\D/', '', $profissional->PROFISSIONAL_WHATSAPP),
        ], [
            'USUARIO_NOME' => ['required', 'string', 'max:150'],
            'USUARIO_CPF' => ['required', 'cpf', Rule::unique('USUARIO')->ignore($usuario->getKey(), 'USUARIO_ID')],
            'USUARIO_CONTATO' => ['required', 'regex:/^[0-9]{10,11}$/', Rule::unique('USUARIO')->ignore($usuario->getKey(), 'USUARIO_ID')],
        ])->validate();
    }

    public function atualizarUsuario(array $dados): Usuario
    {
        return DB::transaction(function () use ($dados) {
            $usuario = Usuario::whereKey($dados['USUARIO_ID'])->lockForUpdate()->firstOrFail();
            $profissional = Profissional::where('USUARIO_ID', $usuario->USUARIO_ID)->lockForUpdate()->first();
            if ($profissional) {
                // An established login is stable even when identity data changes.
                unset($dados['USUARIO_LOGIN']);
                $profissional->PROFISSIONAL_NOME = $dados['USUARIO_NOME'];
                $profissional->PROFISSIONAL_CPF = $dados['USUARIO_CPF'];
                $profissional->PROFISSIONAL_WHATSAPP = $dados['USUARIO_CONTATO'];
                $this->validarUsuario($profissional, $usuario);
                Validator::make($profissional->getAttributes(), [
                    'PROFISSIONAL_NOME' => ['required', 'string', 'max:150'],
                    'PROFISSIONAL_CPF' => ['required', 'cpf', Rule::unique('PROFISSIONAL')->ignore($profissional->getKey(), 'PROFISSIONAL_ID')],
                    'PROFISSIONAL_WHATSAPP' => ['required', 'regex:/^[0-9]{10,11}$/'],
                ])->validate();
                $profissional->save();
                // Credentials are managed by the dedicated password-change flow.
                unset($dados['USUARIO_SENHA']);
            } elseif (!empty($dados['USUARIO_SENHA'])) {
                $dados['USUARIO_SENHA'] = md5($dados['USUARIO_SENHA']);
            } else {
                unset($dados['USUARIO_SENHA']);
            }
            $usuario->fill($dados);
            $usuario->save();
            return Usuario::buscar($usuario->USUARIO_ID);
        }, 3);
    }
}
