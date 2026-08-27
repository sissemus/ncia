@extends('layouts.app')

@section('content')
    <equipe-view :tipos-profissional='@json($tiposProfissional)'></equipe-view>
    
@endsection