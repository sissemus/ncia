@extends('layouts.app')

@section('content')
    <atendimento-equipe-view
        :prioridades='@json($prioridades)'
    ></atendimento-equipe-view>
@endsection
