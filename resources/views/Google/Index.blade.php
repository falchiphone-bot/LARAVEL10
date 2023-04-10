@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;
                ;font-size: 24px; lign=˜Center˜">
                    CALENDÁRIO GOOGLE PARA O SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>
                <div class="badge bg-info text-wrap" style="width: 100%;
                    ;font-size: 24px; lign=˜Center˜">
                    Quantidade de eventos: {{ $eventos->Count() }}
                </div>



                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @elseif (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    <div class="badge bg-warning text-wrap"
                        style="width: 19%;
                                ;font-size: 24px; align=˜center">
                        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                            <a class="btn btn-warning" <a href="Agenda/dashboard">Retornar a lista de opções</a>
                        </nav>
                    </div>


                    <div class="badge bg-success text-wrap"
                        style="width: 20%;
                    ;font-size: 24px; align=˜center˜">


                        <nav class="navbar navbar-success" style="background-color: hsla(234, 92%, 47%, 0.096);">
                            <a class="btn btn-success" <a href="Agenda/create">Novo evento para calendário</a>
                        </nav>
                    </div>
                    @foreach ($eventos as $evento)
                        <p>

                        </p>
                        @if ($evento->name)
                            <div class="badge bg-success text-wrap"
                                style="width: 100%;
                            ;font-size: 24px; lign=˜Center˜">
                                <p>Título: {{ $evento->name }}</p>
                            </div>
                            <p>Identificação: {{ $evento->id }}</p>
                            <p>Ordem: {{ 'verificar' }}</p>

                            <p>Status: @if ($evento->status == 'confirmed')
                                    CONFIRMADO
                                @else
                                    {{ $evento->status }}
                                @endif
                            </p>

                            <p>Transparência:{{ $evento->transparency }}</p>
                            <p>Visibilidade:{{ $evento->visibility }}</p>
                            {{-- <p>Zona de horário: {{ $evento->getTimezone() }}</p> --}}


                            <p>Inicio: {{ $evento->startDateTime->format('d/m/Y H:i:s') }}</p>
                            <p>Fim: {{ $evento->endDateTime->format('d/m/Y H:i:s') }}</p>

                            <p>Atualizado: {{ $evento->updated }}</p>
                            <p>Descrição: {!! $evento->description !!}</p>
                            <p><a href="{{ $evento->htmlLink }}" target="_blank">Abrir no Google calendário</a>
                            </p>

                            <div class="badge bg-warning text-wrap"
                                style="width: 100%;
                            ;font-size: 24px; lign=˜Center˜">
                            </div>



                            <div class="badge bg-primary text-wrap"
                                style="width: 100%;
                                  ;font-size: 24px; align=˜left˜">
                                <div>
                                    <a href="{{ route('Agenda.edit', $evento->id) }}" class="btn btn-success"
                                        tabindex="-1" role="button" aria-disabled="true">Editar</a>



                                    <form method="POST" action="{{ route('Agenda.destroy', $evento->id) }}">
                                        @csrf
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-danger">
                                            Excluir
                                        </button>
                                    </form>
                                </div>

                                <div class="badge bg-warning text-wrap"
                                    style="width: 100%;
                            ;font-size: 24px; lign=˜Center˜">
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

            </div>
        </div>
    </div>

    <div class="b-example-divider"></div>
    </div>
@endsection
