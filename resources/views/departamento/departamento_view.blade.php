@extends('layouts.app')

@section('content')
    <departamento-view
        :hierarquias="{{ $hierarquias->toJson() }}"
    ></departamento-view>
@endsection
