@extends('layouts.app')
@section('content')
    <usuario-view
            :locais="{{ $locais->toJson() }}"
    ></usuario-view>
@endsection
