<?php

namespace App\MyLibs;

use App\Models\Auditoria;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AuditHelper
{
    public static function saveCreated($auditTabela, $auditLinhaId, $auditDepois)
    {
        $auditoria = new Auditoria();
        $auditoria->AUDITORIA_DATA = Carbon::now();
        $auditoria->AUDITORIA_USUARIO_ID = Auth::id();
        $auditoria->AUDITORIA_USUARIO = Auth::user() ? Auth::user()->USUARIO_NOME : 'sistema';
        $auditoria->AUDITORIA_TABELA = $auditTabela;
        $auditoria->AUDITORIA_LINHA_ID = $auditLinhaId;
        $auditoria->AUDITORIA_DEPOIS = $auditDepois;
        $auditoria->AUDITORIA_OPERACAO = "I";
        $auditoria->save();
    }

    public static function saveUpdating($auditTabela, $auditLinhaId, $auditCampo, $auditAntes, $auditDepois)
    {
        $auditoria = new Auditoria();
        $auditoria->AUDITORIA_DATA = Carbon::now();
        $auditoria->AUDITORIA_USUARIO_ID = Auth::id();
        $auditoria->AUDITORIA_USUARIO = Auth::user() ? Auth::user()->USUARIO_NOME : 'sistema';
        $auditoria->AUDITORIA_TABELA = $auditTabela;
        $auditoria->AUDITORIA_LINHA_ID = $auditLinhaId;
        $auditoria->AUDITORIA_CAMPO = $auditCampo;
        $auditoria->AUDITORIA_ANTES = $auditAntes;
        $auditoria->AUDITORIA_DEPOIS = $auditDepois;
        $auditoria->AUDITORIA_OPERACAO = "U";
        $auditoria->save();
    }

    public static function saveDeleting($auditTabela, $auditLinhaId, $auditAntes)
    {
        $auditoria = new Auditoria();
        $auditoria->AUDITORIA_DATA = Carbon::now();
        $auditoria->AUDITORIA_USUARIO_ID = Auth::id();
        $auditoria->AUDITORIA_USUARIO = Auth::user() ? Auth::user()->USUARIO_NOME : 'sistema';
        $auditoria->AUDITORIA_TABELA = $auditTabela;
        $auditoria->AUDITORIA_LINHA_ID = $auditLinhaId;
        $auditoria->AUDITORIA_ANTES = $auditAntes;
        $auditoria->AUDITORIA_OPERACAO = "D";
        $auditoria->save();
    }
}
