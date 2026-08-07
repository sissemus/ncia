@extends('layouts.app')

@section('content')

    <veiculo-view
        :tipos-veiculo="{{ $tiposVeiculo->toJson() }}"
        :situacoes-veiculo="{{ $situacoesVeiculo->toJson() }}"
    ></veiculo-view>

@endsection
