<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificarPrimeiroAcesso
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Verifica se o usuário está logado
        if (Auth::check()) {
            $usuario = Auth::user(); 
            // 2. Se o acesso for nulo E o usuário NÃO estiver tentando ver ou salvar a nova senha
            if (is_null($usuario->USUARIO_ULTIMO_ACESSO) && 
                !$request->routeIs('alteracao-senha') && 
                !$request->routeIs('alterar-senha')) {
                
                return redirect()->route('alteracao-senha')
                    ->with('info', 'Este é seu primeiro acesso. Por favor, altere sua senha para continuar.');
            }
        }

        return $next($request);
    }
}
