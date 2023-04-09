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


                    @foreach ($eventos as $evento)
                    <p>

                            @if(null !== ($evento->getSummary()))
                                {{ $evento->getSummary() }}
                            @else
                                Título não definido
                            @endif

                    </p>
                        <p>Hora: {{ $evento->getStart()->getTimezone() }}</p>
                        <p>Data: {{ $evento->getStart()->getDateTime() }}</p>
                        <p>Descricao: {{ $evento->GetDescription() }}</p>
                    @endforeach




                </div>





            </div>
        </div>

    </div>
    <div class="b-example-divider"></div>
    </div>
@endsection


