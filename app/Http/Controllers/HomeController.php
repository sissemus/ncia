<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $usuarioLogado = Usuario::with('usuarioPerfis')->find(Auth::id());

        return view('home', compact('usuarioLogado'));
    }
}
