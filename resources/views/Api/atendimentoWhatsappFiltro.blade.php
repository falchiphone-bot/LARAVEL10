@if ($NomeAtendido->pagina_refresh == true)
    @include('Api.atendimento.headrefresh')
@endif

@extends('layouts.bootstrap5')

@section('content')
    <div class="py-5 bg-light">
        <div class="container">
            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    ATENDIMENTO - WHATSAPP - SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>

                <div class="card-body">
                     @include('Api.atendimento.mostrasessaoalerta')

                    @can('WHATSAPP - ATENDIMENTO - VER CONTATOS')
                        @include('Api.atendimento.contatostabelamensagens')
                    @endcan
                </div>
            {{-- {{-- </div> --}}
        </div>
    </div>
@endsection

@include('Api.atendimento.scriptsConfirmarRefresh')
