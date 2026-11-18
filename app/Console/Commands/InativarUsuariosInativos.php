<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InativarUsuariosInativos extends Command
{
    protected $signature = 'usuarios:inativar-inativos';

    protected $description = 'Inativa usuários com último acesso há mais de 90 dias';

    public function handle(): int
    {
        $total = 0;

        Usuario::where('USUARIO_ATIVO', 1)
            ->where('USUARIO_ULTIMO_ACESSO', '<', now()->subDays(90))
            ->select('USUARIO_ID')
            ->chunkById(100, function ($usuarios) use (&$total) {
                foreach ($usuarios as $usuario) {
                    $inativado = DB::transaction(function () use ($usuario) {
                        $atual = Usuario::whereKey($usuario->USUARIO_ID)->lockForUpdate()->first();
                        return $atual ? $atual->inativarPorInatividade() : false;
                    }, 3);
                    if ($inativado) {
                        $total++;
                    }
                }
            }, 'USUARIO_ID');

        $this->info("Usuários inativados: {$total}");

        return 0;
    }
}
