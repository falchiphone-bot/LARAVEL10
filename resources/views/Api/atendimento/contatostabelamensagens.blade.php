@include('Api.atendimento.tabelacontatos')

<div class="col-9">

    <div class="card-body">
        @can('WHATSAPP - LISTAR')
            <div class="card-footer">

                <a href="{{ route('whatsapp.indexlista') }}">Retornar para a lista</a>

            </div>
        @endcan

        @can('WHATSAPP - VISUALIZAR MENSAGENS SEM ATENDER')
            @include('Api.atendimento.cabecalhotabelamensagens')a
            @include('Api.atendimento.tabelamensagens')
        @endcan

    </div>
</div>

