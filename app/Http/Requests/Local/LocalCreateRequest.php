<?php

namespace App\Http\Requests\Local;

use Illuminate\Foundation\Http\FormRequest;

class LocalCreateRequest extends FormRequest {
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            "local.LOCAL_DESCRICAO"     => ["required"],
            "local.LOCAL_ENDERECO"      => ["required"],
            "local.LOCAL_ABERTURA"      => ["required"],
            "local.LOCAL_FECHAMENTO"    => ["required"],
            "local.LOCAL_TIPO"          => ["required"],
            "local.LOCAL_ATIVO"         => ["required"],
            "publico.PUBLICO_DESCRICAO" => ["required"],
        ];
    }

    public function attributes() {
        return [
            "local.LOCAL_DESCRICAO"     => "<b>DESCRIÇÃO</b>",
            "local.LOCAL_ENDERECO"      => "<b>ENDEREÇO</b>",
            "local.LOCAL_ABERTURA"      => "<b>ABERTURA</b>",
            "local.LOCAL_FECHAMENTO"    => "<b>FECHAMENTO</b>",
            "local.LOCAL_TIPO"          => "<b>TIPO DE LOCAL</b>",
            "local.LOCAL_ATIVO"         => "<b>ATIVO</b>",
            "publico.PUBLICO_DESCRICAO" => "<b>PÚBLICO ALVO</b>",
        ];
    }
}
