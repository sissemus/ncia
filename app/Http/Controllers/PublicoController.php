<?php

namespace App\Http\Controllers;

use App\Models\Publico;
use Illuminate\Http\Request;

class PublicoController extends Controller {
    public function getByLocalId(Request $request) {
        $localId = $request->input("localId");
        return response(Publico::getByLocalId($localId));
    }
}
