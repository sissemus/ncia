<?php

namespace App\Http\Controllers;

use App\Models\Local;
use App\Models\LocalSituacao;
use App\Models\Situacao;
use App\Models\UsuarioLocal;
use App\Models\Vacinacao;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class HomeController extends Controller {

    public function __construct() {
        // $this->middleware('auth');
    }

    public function index() {
        $a = Local::with([
            "vacinaLocais.vacina",
            "vacinaLocais.vacinacoes.dose",
        ])->get();
        return view('home')
            ->with([
                "usuarioLocais" => UsuarioLocal::getByUsuarioId(auth()->id()),
                "situacoes" => Situacao::all(),
                "a" => $a
            ]);
    }

    public function publico(){
        $locais = Local::with([
            "vacinaLocais" => function($query){
                $query->whereDate('VACINA_LOCAL_DH_CADASTRO',Carbon::now());
            },
            "vacinaLocais.vacina",
            // "vacinaLocais.vacinacoes" => function($query){
            //     $query->sum('VACINACAO_QTD')
            //     ->groupBy("DOSE_ID");
            // },
            "vacinaLocais.vacinacoes.dose",
        ])
        // ->withSum("vacinaLocais.vacinacoes",'VACINACAO_QTD')
        // ->whereHas('vacinaLocais')
        ->whereHas('vacinaLocais',function(Builder $query){
            $query->whereDate('VACINA_LOCAL_DH_CADASTRO',Carbon::now());
        })
        ->where('LOCAL_ATIVO',1)
        ->get();

        return view('welcome')
            ->with([
                "totalPostosAtivos" => LocalSituacao::totalPostosAtivos(),
                "locais" => $locais,
            ]);
    }
}
