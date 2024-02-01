<?php

namespace App\Observers;

use App\Models\LocalSituacao;
use Illuminate\Support\Facades\Log;

class LocalSituacaoObserver {
    public function creating(LocalSituacao $localSituacao) {
        $total = LocalSituacao::with([])->where("LOCAL_ID", $localSituacao->LOCAL_ID)->count();
        if ($total > 0) {
            LocalSituacao::with([])
                ->where("LOCAL_ID", $localSituacao->LOCAL_ID)
                ->update([
                    "LOCAL_SITUACAO_ULTIMO" => null
                ]);
        }
        $localSituacao->LOCAL_SITUACAO_DATA = date("Y-m-d H:i:s");
        $localSituacao->LOCAL_SITUACAO_ULTIMO = 1;
    }
}
