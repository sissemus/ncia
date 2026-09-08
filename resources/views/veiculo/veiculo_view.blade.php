@extends('layouts.app')

@section('content')

    <veiculo-view
        :tipos-veiculo="{{ $tiposVeiculo->toJson() }}"
        :situacoes-veiculo="{{ $situacoesVeiculo->toJson() }}"
        :unidades="{{ $unidades->toJson() }}"
    ></veiculo-view>

@endsection
