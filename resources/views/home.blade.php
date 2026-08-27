@extends('layouts.app')

@section('content')
    <home :prioridades='@json($prioridades)'></home>
@endsection
