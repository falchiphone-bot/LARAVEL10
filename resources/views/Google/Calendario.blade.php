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
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @elseif (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                        <a class="btn btn-warning" href="/Google/dashboard">Retornar a lista de opções</a> </nav>


                    @foreach ($eventos as $evento)
                        <p>



                        </p>
                        @if (null !== $evento->getSummary())
                        <div class="badge bg-success text-wrap" style="width: 100%;
                            ;font-size: 24px; lign=˜Center˜">
                             <p>Título: {{ $evento->getSummary() }}</p>
                            </div>
                             <p>Identificação: {{ $evento->getId()}}</p>
                             <p>Ordem: {{ $evento->getSequence() }}</p>

                             <p>Status:{{ $evento->getStatus()}}</p>
                             <p>Transparência:{{ $evento->getTransparency() }}</p>
                             <p>Visibilidade:{{ $evento->getVisibility() }}</p>
                             {{-- <p>Zona de horário: {{ $evento->getTimezone() }}</p> --}}

                            <p>Atualizado: {{ $evento->getUpdated() }}</p>
                            <p>Descrição: {{ $evento->GetDescription() }}</p>
                            <p>Abrir no Google calendário: <a href="{{ $evento->htmlLink }}" target="_blank">{{ $evento->htmlLink }}</a></p>

                            <div class="badge bg-success text-wrap" style="width: 100%;
                            ;font-size: 24px; lign=˜Center˜">

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
