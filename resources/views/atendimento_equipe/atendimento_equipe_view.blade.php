@extends('layouts.app')

@section('content')
    <atendimento-equipe-view
        :prioridades='@json($prioridades)'
        :tipos-chamado='@json($tiposChamado)'
        :sexos='@json($sexos)'
        :tipos-precaucao='@json($tiposPrecaucao)'
        :suportes-o2='@json($suportesO2)'
        :suportes-hemodinamicos='@json($suportesHemodinamicos)'
    ></atendimento-equipe-view>
@endsection
