<?php

namespace App\Rules\Usuario;

use App\Models\UsuarioUnidade;
use Illuminate\Contracts\Validation\Rule;

class UnicoUsuarioUnidade implements Rule
{
    private $usuarioId;

    public function __construct($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    }

    public function passes($attribute, $value)
    {
        $unico = UsuarioUnidade::where('USUARIO_ID', $this->usuarioId)
            ->where('UNIDADE_ID', $value)
            ->first();

        return $unico ? false : true;
    }

    public function message()
    {
        return 'Este usuário já possui essa unidade vinculada a ele.';
    }
}