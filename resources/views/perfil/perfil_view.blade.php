@extends('layouts.app')

@section('content')
    <perfil-view
        :aplicacoes="{{ $aplicacoes->toJson() }}"
    ></perfil-view>
@endsection
