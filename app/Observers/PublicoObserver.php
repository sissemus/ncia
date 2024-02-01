<?php

namespace App\Observers;

use App\Models\Publico;

class PublicoObserver {
    public function creating(Publico $publico) {
        $total = Publico::with([])->where("LOCAL_ID", $publico->LOCAL_ID)->count();
        if ($total > 0) {
            Publico::with([])
                ->where("LOCAL_ID", $publico->LOCAL_ID)
                ->update([
                    "PUBLICO_ULTIMO" => null
                ]);
        }
        $publico->PUBLICO_DATA = date("Y-m-d H:i:s");
        $publico->PUBLICO_ULTIMO = 1;
    }
}
