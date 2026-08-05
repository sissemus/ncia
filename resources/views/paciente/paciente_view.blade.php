@extends('layouts.app')

@section('content')
    <paciente-view
        :sexos="{{ $sexos->toJson() }}"
    ></paciente-view>
@endsection