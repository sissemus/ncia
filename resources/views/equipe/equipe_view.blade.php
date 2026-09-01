@extends('layouts.app')

@section('content')
    <equipe-view :tipos-profissional='@json($tiposProfissional)'
                 :profissionais='@json($profissionais)'
    ></equipe-view>
    
@endsection