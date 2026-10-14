@extends('layouts.app')

@section('content')
    <atendimento-equipe-view
        :prioridades='@json($prioridades)'
        :tipos-chamado='@json($tiposChamado)'
    ></atendimento-equipe-view>
@endsection
