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
                        <a class="btn btn-warning" href="/Google/dashboard">Retornar a lista de opções</a>
                    </nav>
                </div>
                <form action="{{ route('Agenda.update',$evento->id)}}" method="POST">
                   <input type="hidden" value="PUT" name="_method">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            EDITAR EVENTO
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-12">
                                    <label for="name">Nome do evento</label>
                                    <input required type="text" class="form-control" id='name' name="name" value="{{ $evento->name }}">
                                </div>
                                <div class="col-sm-6">
                                    <label for="inicio">Início do evento</label>
                                    <input required type="datetime-local" class="form-control" id='inicio' name="inicio" value="{{ $evento->startDateTime->format('Y-m-d\TH:i') }}">
                                </div>
                                <div class="col-sm-6">
                                    <label for="fim">Fim do evento</label>
                                    <input required type="datetime-local" class="form-control" id='fim' name="fim" value="{{ $evento->endDateTime->format('Y-m-d\TH:i') }}">
                                </div>
                                <div class="col-sm-12">
                                    <label for="fim">Descrição do evento</label>
                                    <textarea required name="descricao" id="descricao" cols="30" rows="10" class="form-control ">{{ $evento->description }}

                                    </textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button class='btn btn-primary'>Salvar a edição do evento</button>
                        </div>

                    </div>
                </form>



            </div>
        </div>

    </div>
    <div class="b-example-divider"></div>
    </div>
@endsection
