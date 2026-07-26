<?php

namespace App\Providers;

use App\Observers\Auditables;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register()
    {
    }

    public function boot()
    {
        $versao = "5.0.0";
        Config::set(['versao' => $versao]);
        $caracteresRemocao = ["/[^0-9]/"];
        Config::set(['vcp' => $versao]);
        Config::set(['vsp' => preg_replace($caracteresRemocao, "", $versao)]);
        Config::set(['APP_NAME' => 'NOVO CIA']);
        Config::set(['APP_DESCRICAO' => 'Central Interna de Ambulância']);

        Auditables::register();
    }
}
