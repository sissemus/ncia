@extends('layouts.app')

@section('content')
    <profissional-view
        :sexos="{{ $sexos->toJson() }}"
        :tipos_profissional="{{ $tiposProfissional->toJson() }}"
    ></profissional-view>
@endsection