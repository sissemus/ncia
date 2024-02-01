<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
{{--    <meta name="viewport" content="width=device-width, initial-scale=1">--}}
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="shortcut icon" type="image/png" href="{{ asset('img/favicons.png') }}"/>
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v={{ config('vsp') }}" rel="stylesheet">
    <title>{{ config('APP_NAME') }}{{' v'.config('vcp')}}</title>
    <style>
        .swal2-popup {
            font-family: 'Nunito', sans-serif !important;
        }
    </style>
</head>
<body>
<div id="app">
    <app
            :menus="{{ json_encode($menus) }}"
            :usuario="{{ $usuario }}"
            app-name="{{ config('APP_NAME') }}"
    >
        <template v-slot:conteudo>
            @yield('content')
        </template>
    </app>
</div>
<script src="{{ asset('js/app.js') }}?v={{ config('vsp') }}" defer></script>
</body>
</html>
