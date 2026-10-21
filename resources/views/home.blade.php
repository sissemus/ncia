@extends('layouts.app')

@section('content')
    <home
        :usuario_logado='@json($usuarioLogado)'
        :prioridades='@json($prioridades)'
        :tipos-chamado='@json($tiposChamado)'
    ></home>
@endsection
