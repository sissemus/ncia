<?php

namespace App\Http\Controllers;

use App\Http\Requests\Perfil\PerfilCreateRequest;
use App\Http\Requests\Perfil\PerfilUpdateRequest;
use App\Models\Acesso;
use App\Models\Aplicacao;
use App\Models\Perfil;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerfilController extends Controller
{
    public function view()
    {
        return view("perfil.perfil_view")
            ->with([
                "aplicacoes" => Aplicacao::listAll()
            ]);
    }

    public function create(PerfilCreateRequest $request)
    {
        DB::beginTransaction();

        try {
            $perfil = Perfil::create($request->validated());

            $acessosJson = collect($request->post('acessos', []));

            foreach ($acessosJson as $acessoData) {
                $acesso = new Acesso($acessoData);
                $acesso->PERFIL_ID = $perfil->PERFIL_ID;
                $acesso->save();
            }

            DB::commit();

            return response(Perfil::getById($perfil->PERFIL_ID));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(PerfilUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            $perfil = Perfil::findOrFail($request->input('PERFIL_ID'));
            $perfil->fill($request->validated());
            $perfil->save();

            $acessosIds = collect($request->input('acessos', []))
                ->pluck('APLICACAO_ID')
                ->filter()
                ->toArray();

            foreach ($acessosIds as $aplicacaoId) {
                Acesso::updateOrCreate(
                    [
                        'PERFIL_ID' => $perfil->PERFIL_ID,
                        'APLICACAO_ID' => $aplicacaoId,
                    ],
                    [
                        'ACESSO_ATIVO' => 1
                    ]
                );
            }

            Acesso::where('PERFIL_ID', $perfil->PERFIL_ID)
                ->whereNotIn('APLICACAO_ID', $acessosIds)
                ->delete();

            DB::commit();
            return response(Perfil::getById($perfil->PERFIL_ID));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function search(Request $request)
    {
        $perfil = Perfil::search($request->input("valorPesquisa"))
            ->when($request->orderBy, function (Builder $query) use ($request) {
                $request->sort = $request->sort ?: 'asc';
                $query->orderBy($request->orderBy, $request->sort);
            })
            ->when(!$request->orderBy, function (Builder $query) {
                $query->orderBy('PERFIL_ID');
            })
            ->paginate();
        return response($perfil);
    }

    public function list()
    {
        return response(Perfil::listAll());
    }
}
