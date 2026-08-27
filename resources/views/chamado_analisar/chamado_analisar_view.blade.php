@extends("layouts.app")

@section("content")
<chamado-analisar-view
    :prioridades='@json($prioridades)'
    :situacoes-chamado='@json($situacoesChamado)'
    :sexos='@json($sexos)'
    :tipos-chamado='@json($tiposChamado)'
    :tipos-precaucao='@json($tiposPrecaucao)'
    :suportes-o2='@json($suportesO2)'
    :suportes-hemodinamicos='@json($suportesHemodinamicos)'
    :temperaturas='@json($temperaturas)'
    :frequencias-cardiacas='@json($frequenciasCardiacas)'
    :pressoes-arteriais='@json($pressoesArteriais)'
    :saturacoes='@json($saturacoes)'
    :motivos-cancelamento='@json($motivosCancelamento)'
></chamado-analisar-view>
@endsection
