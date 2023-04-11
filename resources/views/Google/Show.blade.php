@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;
                ;font-size: 24px; lign=˜Center˜">
                    CALENDÁRIO GOOGLE PARA O SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>


                <div class="card-body">

                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                        <a class="btn btn-warning" href="/Agenda">Retornar a lista de eventos</a>
                    </nav>
                </div>
                <form action="{{ route('Agenda.update', $evento->id) }}" method="POST">
                    <input type="hidden" value="PUT" name="_method">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <div class="badge bg-primary text-wrap"
                                style="width: 100%;
                ;font-size: 16px; lign=˜Center˜">
                                VISUALIZAÇÃO DOS DADOS DO EVENTO
                            </div>

                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="card-header">
                                    <div class="badge bg-primary text-wrap"
                                        style="width: 100%;
                                         ;font-size: 24px; lign=˜Left˜">
                                        <div class="col-sm-12">
                                            <label for="name">Nome do evento</label>
                                            <input required type="text" class="form-control" id='name'
                                                name="name" value="{{ $evento->name }} " disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-header">
                                    <div class="badge bg-primary text-wrap"
                                        style="width: 100%;
                                         ;font-size: 16px; lign=˜Center˜">
                                        <div class="col-sm-6">

                                            <p>Inicio do evento: {{ $evento->startDateTime->format('d/m/Y H:i:s') }}</p>
                                        </div>
                                        <div class="col-sm-6">

                                            <p>Fim do evento: {{ $evento->endDateTime->format('d/m/Y H:i:s') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-header">
                                    <div class="badge bg-primary text-wrap"
                                        style="width: 100%;
                        ;font-size: 24px; lign=˜Center˜">


                                        <div class="col-sm-12">
                                            <label for="fim">Descrição do evento</label>
                                            <textarea required name="descricao" id="descricao" cols="30" rows="10" class="form-control " disabled>{{ $evento->description }}

                                    </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- <div class="card-footer">
                                <button class='btn btn-primary'>Salvar a edição do evento</button>
                            </div> --}}

                        </div>
                </form>
                @foreach ($participantes as $participante)
                <div class="card-header">
                    <div class="badge bg-primary text-wrap"
                        style="width: 100%;
                         ;font-size: 16px; lign=˜Center˜">
                        <div class="col-sm-6">

                            <p>Nome: {{ $participante->displayName}}</p>
                        </div>
                        <div class="col-sm-6">
                            <p>Email: {{ $participante->email  }}</p>

                        </div>
                    </div>
                </div>
                @endforeach


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
