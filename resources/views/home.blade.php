@extends('layouts.app')

@section('content')
    <home
        :usuario_logado='@json($usuarioLogado)'
        :prioridades='@json($prioridades)'
    ></home>
@endsection
