<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profissional\ProfissionalCreateRequest;
use App\Http\Requests\Profissional\ProfissionalUpdateRequest;
use App\Models\Profissional;
use App\Models\TabelaGenerica;
use Illuminate\Http\Request;

class ProfissionalController extends Controller
{
    public function view()
    {   
        $sexos = TabelaGenerica::sexo();
        $tiposProfissional = TabelaGenerica::tipoProfissional();
        return view("profissional.profissional_view", compact('sexos', 'tiposProfissional'));
    }

    public function inserir(ProfissionalCreateRequest $request)
    {
        $profissional = new Profissional($request->input());
        $profissional->save();

        return response([
            "cod" => 1,
            "msg" => "Profissional cadastrado com sucesso",
            "retorno" => Profissional::buscar($profissional->PROFISSIONAL_ID)
        ], 200);
    }

    public function alterar(ProfissionalUpdateRequest $request)
    {
        $profissional = Profissional::buscar($request->PROFISSIONAL_ID);
        $profissional->fill($request->input());
        $profissional->update();

        return response([
            "cod" => 1,
            "msg" => "Profissional alterado com sucesso",
            "retorno" => Profissional::buscar($profissional->PROFISSIONAL_ID)
        ], 200);
    }

    public function buscar($id)
    {
        return response([
            "cod" => 1,
            "msg" => "Profissional encontrado com sucesso",
            "retorno" => Profissional::buscar($id)
        ], 200);
    }

    public function listar(Request $request)
    {
        $profissionais = Profissional::listar($request)->get();

        return response([
            "cod" => 1,
            "msg" => "Profissionais listados com sucesso",
            "retorno" => $profissionais
        ], 200);
    }

    public function pesquisar(Request $request)
    {
        $profissionais = Profissional::listar($request)
            ->paginate(15);

        return response([
            "cod" => 1,
            "msg" => "Profissionais pesquisados com sucesso",
            "retorno" => $profissionais
        ], 200);
    }

    public function deletar(Request $request)
    {
        $profissional = Profissional::buscar($request->PROFISSIONAL_ID);
        $profissional->PROFISSIONAL_ATIVO = 0;
        $profissional->update();

        return response([
            "cod" => 1,
            "msg" => "Profissional inativado com sucesso",
            "retorno" => Profissional::buscar($profissional->PROFISSIONAL_ID)
        ], 200);
    }
}