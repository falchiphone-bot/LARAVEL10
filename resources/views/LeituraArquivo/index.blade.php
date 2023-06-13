@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                    LEITURA DE ARQUIVOS DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>
            </div>


            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                        {{session(['success' => null]) }}
                    </div>
                @elseif (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    {{session(['error' => null]) }}
                @endif
                @if (session('Lancamento'))
                    <div class="alert alert-danger">
                        {{ session('Lancamento') }}
                    </div>
                    {{session(['Lancamento' => null]) }}
                @endif

                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-warning" href="/Contabilidade">Retornar a lista de opções</a>
                    {{-- <a class="btn btn-primary" href="/LeituraArquivo">Último arquivo enviado de extrado cartão de crédito</a> --}}

                     @can('HISTORICOS - LISTAR')
                            <a class="btn btn-success" href="/Historicos">Históricos para lançamentos

                                contábeis</a>
                        @endcan

                    @can('LEITURA DE ARQUIVO - LISTAR')
                      <a class="btn btn-secondary" href="/LeituraArquivo/SomenteLinha">Selecionar linha, ou enviar arquivo e conciliar extrato cartão de crédito</a>
                    @endcan

                </nav>





                {{-- @can('MOEDAS- INCLUIR')
                    <a href="{{ route('Moedas.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Incluir nome de moedas</a>
                @endcan
                <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                        <p>Total de moedas cadastradas no sistema de gerenciamento administrativo e contábil:
                            {{ $moedas->count() ?? 0 }}</p>
                    </div>
                </div> --}}

                 @can('LEITURA DE ARQUIVO - VISUALIZAR')
                <table>
                    {{-- <form action="/LeituraArquivo/GerarPDF" method="post">
                        <button type="submit" name="GerarPDF" value="true">Gerar PDF</button>
                      </form> --}}
                      <a class="btn btn-danger" href="/LeituraArquivo/GerarPDF" target="_blank">Gerar PDF</a>

                    <thead>
                        <tr>
                            <th>#</th>
                            @foreach (range(1, count($cellData[1])) as $column)
                                <th>Column {{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cellData as $rowIndex => $rowData)
                            <tr>
                                <td>{{ $rowIndex }}</td>
                                @foreach ($rowData as $cellValue)
                                    <td>{{ $cellValue }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
@endcan



            </div>

            <tbody>

                                <div class="badge bg-primary text-wrap" style="width: 100%;">
        </div>
    </div>

    </div>
    <div class="b-example-divider"></div>
    </div>
@endsection
