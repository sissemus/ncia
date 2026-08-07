@extends('layouts.app')

@section('content')
    <veiculo-unidade-view
        :veiculos="{{ $veiculos->toJson() }}"
        :unidades="{{ $unidades->toJson() }}"
    ></veiculo-unidade-view>
@endsection
