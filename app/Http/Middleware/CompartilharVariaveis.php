<?php

namespace App\Http\Middleware;

use App\Models\Acesso;
use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class CompartilharVariaveis {
        public function handle(Request $request, Closure $next) {
        View::share([
            'usuario' => Usuario::getById(auth()->id()),
            "aplicacoes" => Acesso::getByUsuarioId(auth()->id())
        ]);
        return $next($request);
    }
}
