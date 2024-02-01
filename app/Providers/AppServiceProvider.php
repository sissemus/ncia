<?php

namespace App\Providers;

use App\Models\LocalSituacao;
use App\Models\Publico;
use App\Observers\LocalSituacaoObserver;
use App\Observers\PublicoObserver;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    public function register() {
        //
    }

    public function boot() {
        $versao = "1.0.1";
        Config::set(['versao' => $versao]);
        $caracteresRemocao = ["/[^0-9]/"];
        Config::set(['vcp' => $versao]);
        Config::set(['vsp' => preg_replace($caracteresRemocao, "", $versao)]);
        Config::set(['APP_NAME' => 'Projeto Base']);
        Config::set(['APP_DESCRICAO' => 'Divulgação de Vacinas Aplicadas']);

        LocalSituacao::observe(LocalSituacaoObserver::class);
        Publico::observe(PublicoObserver::class);
    }
}
