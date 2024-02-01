<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class CompartilharVariaveis {
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        View::share([
            'menus' => Usuario::getUserMenu(),
            'usuario' => Usuario::getById(auth()->id()),
//            'loja' => $request->session()->get('loja')
        ]);
        return $next($request);
    }
}
