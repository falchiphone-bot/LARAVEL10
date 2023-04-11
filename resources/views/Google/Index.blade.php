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

                    @can('AGENDA - INCLUIR')
                        <div class="badge bg-success text-wrap"
                            style="width: 20%;
                    ;font-size: 24px; align=˜center˜">
                            <nav class="navbar navbar-success" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                <a class="btn btn-success" <a href="Agenda/create">Novo evento para calendário</a>
                            </nav>
                        </div>
                    @endcan


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
                                    <table class="table" style="background-color: rgb(247, 247, 213);">
                                        <thead>
                                            <tr>
                                                @can('AGENDA - EDITAR')
                                                    <th scope="col" class="px-6 py-4">
                                                        <a href="{{ route('Agenda.edit', $evento->id) }}"
                                                            class="btn btn-success" tabindex="-1" role="button"
                                                            aria-disabled="true">Editar</a>
                                                    </th>
                                                @endcan

                                                @can('AGENDA - VER')
                                                    <th scope="col" class="px-6 py-4">
                                                        <a href="{{ route('Agenda.show', $evento->id) }}"
                                                            class="btn btn-info" tabindex="-1" role="button"
                                                            aria-disabled="true">VER</a>
                                                    </th>
                                                @endcan

                                                @can('AGENDA - EXCLUIR')
                                                    <th scope="col" class="px-6 py-4">
                                                        <form method="POST"
                                                            action="{{ route('Agenda.destroy', $evento->id) }}">
                                                            @csrf
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <button type="submit" class="btn btn-danger">
                                                                Excluir
                                                            </button>
                                                        </form>
                                                    </th>
                                                @endcan
                                            </tr>
                                        </thead>

                                    </table>
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

@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });

        $('form').submit(function(e) {
            e.preventDefault();
            $.confirm({
                title: 'Confirmar!',
                content: 'Confirma?',
                buttons: {
                    confirmar: function() {
                        // $.alert('Confirmar!');
                        $.confirm({
                            title: 'Confirmar!',
                            content: 'Deseja realmente continuar?',
                            buttons: {
                                confirmar: function() {
                                    // $.alert('Confirmar!');
                                    e.currentTarget.submit()
                                },
                                cancelar: function() {
                                    // $.alert('Cancelar!');
                                },

                            }
                        });

                    },
                    cancelar: function() {
                        // $.alert('Cancelar!');
                    },

                }
            });
        });
    </script>
@endpush
