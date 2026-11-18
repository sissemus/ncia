<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        return DB::transaction(function () use ($request) {
            $user = Usuario::where([
                'USUARIO_LOGIN' => $request->post('USUARIO_LOGIN'),
                'USUARIO_SENHA' => md5($request->post('USUARIO_SENHA')),
                'USUARIO_ATIVO' => 1
            ])->lockForUpdate()->first();

            if (!$user || $user->inativarPorInatividade()) {
                return false;
            }

            if ($user->USUARIO_VIGENCIA !== null && Carbon::parse($user->USUARIO_VIGENCIA)->lessThan(Carbon::now())) {
                return false;
            }

            $user->USUARIO_ULTIMO_ACESSO = now();
            $user->save();
            Auth::login($user, false);

            return true;
        }, 3);
    }

    protected function validateLogin(Request $request) {
        $request->validate([$this->username() => 'required|string', 'USUARIO_SENHA' => 'required|string']);
    }
}
