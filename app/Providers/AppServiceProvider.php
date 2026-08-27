<?php

namespace App\Providers;

use App\Observers\Auditables;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{

    public function register()
    {
    }

    public function boot()
    {
        Schema::defaultStringLength(191);
        $versao = "1.0.2";
        Config::set(['versao' => $versao]);
        $caracteresRemocao = ["/[^0-9]/"];
        Config::set(['vcp' => $versao]);
        Config::set(['vsp' => preg_replace($caracteresRemocao, "", $versao)]);
        Config::set(['APP_NAME' => 'NOVO CIA']);
        Config::set(['APP_DESCRICAO' => 'Central Interna de Ambulância']);

        Auditables::register();
    }
}
