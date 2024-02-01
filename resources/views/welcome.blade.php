{{--@extends('adminlte::auth.login')--}}

        <!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
{{--    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">--}}
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">

    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="refresh" content="300000; URL=#">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link rel="shortcut icon" type="image/png" href="{{ asset('img/favicons.png') }}"/>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <title>{{ config('APP_NAME', 'Filômetro') }}</title>
</head>
<body>
<div id="app">
    <public
            app-name="{{ config('APP_NAME') .' - '. config('APP_DESCRICAO').' v'.config('vcp') }}"
            :total-postos-ativos="{{ $totalPostosAtivos }}"
            :locais="{{ $locais->toJson() }}"
    ></public>
</div>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
