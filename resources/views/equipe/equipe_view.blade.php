@extends('layouts.app')

@section('content')
    <equipe-view :tipos-profissional='@json($tiposProfissional)'
                 :profissionais='@json($profissionais)'
                 :tipos-veiculo='@json($tiposVeiculo)'
    ></equipe-view>
    
@endsection