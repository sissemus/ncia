<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LoginController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm() {
        return view('auth.login');
    }

    public function username(): string {
        return "USUARIO_LOGIN";
    }

    protected function attemptLogin(Request $request): bool {
        $login = trim((string) $request->post('USUARIO_LOGIN'));

        $motivo = DB::transaction(function () use ($request, $login) {
            $usuarios = Usuario::where('USUARIO_LOGIN', $login)->lockForUpdate()->get();
            if ($usuarios->isEmpty()) {
                return 'login_inexistente';
            }

            $hash = md5((string) $request->post('USUARIO_SENHA'));
            $candidatos = $usuarios->filter(function (Usuario $u) use ($hash) {
                return strtolower((string) $u->USUARIO_SENHA) === $hash;
            });
            $user = $candidatos->firstWhere('USUARIO_ATIVO', 1) ?? $candidatos->first();
            if (!$user) {
                return 'senha_incorreta';
            }

            if ($user->USUARIO_ATIVO !== 1) {
                return 'usuario_inativo';
            }

            if ($user->inativarPorInatividade()) {
                return 'inativado_por_inatividade';
            }

            if ($user->USUARIO_VIGENCIA !== null && Carbon::parse($user->USUARIO_VIGENCIA)->lessThan(Carbon::now())) {
                return 'vigencia_expirada';
            }

            $user->USUARIO_ULTIMO_ACESSO = now();
            $user->save();
            Auth::login($user, false);

            return null;
        }, 3);

        if ($motivo === null) {
            return true;
        }

        // Diagnóstico de falhas de login (nunca registrar a senha).
        Log::warning('login_falhou', [
            'login' => $login,
            'motivo' => $motivo,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return false;
    }

    protected function validateLogin(Request $request) {
        $request->validate([$this->username() => 'required|string', 'USUARIO_SENHA' => 'required|string']);
    }
}
