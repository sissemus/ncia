<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UsuarioAdmin {
    public function handle(Request $request, Closure $next) {
        if ($request->hasSession() && $request->getRequestUri() != '/') {
            if (Auth::user()->USUARIO_ADM != 1) {
                return redirect('home');
            }
        }
        return $next($request);
    }
}
