<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlterarSenha
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $usuario = Auth::user();

        if (mb_strtoupper($usuario->USUARIO_LOGIN, 'UTF-8') === 'DES') {
            return $next($request);
        }

        if ($usuario->USUARIO_SENHA === md5('123')) {
            if (!$request->routeIs(
                'alteracao-senha',
                'alterar-senha'
            )) {
                return redirect()->route('alteracao-senha');
            }
        }

        return $next($request);
    }
}