<?php

namespace App\Http\Controllers;

use App\Http\Requests\Paciente\PacienteCreateRequest;
use App\Http\Requests\Paciente\PacienteUpdateRequest;
use App\Models\Paciente;
use App\Models\TabelaGenerica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PacienteController extends Controller
{
    public function view()
    {
        $sexos = TabelaGenerica::sexo();

        return view("paciente.paciente_view", compact("sexos"));
    }

    public function inserir(PacienteCreateRequest $request)
    {
        $paciente = DB::transaction(function () use ($request) {
            $paciente = new Paciente($request->validated());
            $paciente->USUARIO_ID = Auth::id();
            $paciente->PACIENTE_DT_CAD = now();
            $paciente->PACIENTE_DT_IDENTIFICACAO = now();
            $paciente->save();

            return $paciente;
        });

        return response([
            "cod" => 1,
            "msg" => "Paciente cadastrado com sucesso",
            "retorno" => Paciente::buscar($paciente->PACIENTE_ID)
        ], 200);
    }

    public function alterar(PacienteUpdateRequest $request)
    {
        $paciente = DB::transaction(function () use ($request) {
            $paciente = Paciente::findOrFail($request->PACIENTE_ID);
            $paciente->fill($request->validated());

            if (!$paciente->PACIENTE_DT_IDENTIFICACAO)
                $paciente->PACIENTE_DT_IDENTIFICACAO = now();

            $paciente->save();

            return $paciente;
        });

        return response([
            "cod" => 1,
            "msg" => "Paciente alterado com sucesso",
            "retorno" => Paciente::buscar($paciente->PACIENTE_ID)
        ], 200);
    }

    public function buscar($id)
    {
        return response([
            "cod" => 1,
            "msg" => "Paciente encontrado com sucesso",
            "retorno" => Paciente::buscar($id)
        ], 200);
    }

    public function buscarPorCpf(Request $request)
    {
        $cpf = preg_replace("/\D/", "", $request->PACIENTE_CPF);
        $paciente = Paciente::where("PACIENTE_CPF", $cpf)->first();

        return response([
            "cod" => 1,
            "msg" => $paciente ? "Paciente encontrado com sucesso" : "CPF não cadastrado",
            "retorno" => $paciente ? Paciente::buscar($paciente->PACIENTE_ID) : null
        ], 200);
    }

    public function listar(Request $request)
    {
        $pacientes = Paciente::pesquisar($request)->get();

        return response([
            "cod" => 1,
            "msg" => "Pacientes listados com sucesso",
            "retorno" => $pacientes
        ], 200);
    }

    public function pesquisar(Request $request)
    {
        $pacientes = Paciente::pesquisar($request)->paginate(15);

        return response([
            "cod" => 1,
            "msg" => "Pacientes pesquisados com sucesso",
            "retorno" => $pacientes
        ], 200);
    }
}
