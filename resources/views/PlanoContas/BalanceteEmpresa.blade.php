@extends('layouts.bootstrap5')
@section('content')
    <div class="py-2 bg-light">
        <div class="container">
            {{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Permissions</a></li>
              <li class="breadcrumb-item active" aria-current="page">edit</li>
            </ol>
          </nav> --}}

            <div class="card">
                @can('CONTABILIDADE - LISTAR')
                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                        <a class="btn btn-success" href="/PlanoContas/Balancetes">Balancete por período e empresa
                            selecionada</a>
                    </nav>
                @endcan
                @can('EMPRESAS - LISTAR')
                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                        <a class="btn btn-primary" href="/Empresas">Selecionar empresa</a>
                    </nav>
                @endcan

                <div class="badge bg-success text-wrap" style="width: 100%;">
                    Contas de {{ session('Empresa')->Descricao }}
                </div>
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    Período de {{ \Carbon\Carbon::parse($retorno['DataInicial'])->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($retorno['DataFinal'])->format('d/m/Y') }}
                </div>

                <hr>



                <table class="table table-bordered">

                    <tr>
                        <th>Descrição</th>
                        <th>Saldo atual</th>
                        <th>Classificação</th>
                        <th>Grau</th>
                    </tr>
                      @php
                    $Codigo = null
               @endphp
                    @foreach ($contasEmpresa as $conta)


                @php
                  $Codigoatual = substr($conta['Codigo'], 0, 1)
                @endphp

                    @if ($Codigo !== $Codigoatual)
                        <tr>
                            <td>
                                <div class="badge bg-warning text-wrap" style="width: 100%; text-align: right;">
                                     TOTAL DO ATIVO
                                </div>

                                <td>
                                    <div class="badge bg-warning text-wrap" style="width: 100%; text-align: right;">
                                        {{ number_format($somaSaldoAtual, 2, ',', '.') }}
                                    </div>
                                </td>
                            </td>
                        </tr>
                    @endif


                        <tr>
                            <td style="text-align: left;">

                                @php($c = 0)
                                @while ($c < substr_count($conta['Codigo'], '.'))
                                    &nbsp;&nbsp;
                                    @php($c++)
                                @endwhile

                                @if ($conta['Grau'] == '1')
                                    <div class="badge bg-primary text-wrap" style="width: 100%;">
                                        {{ $conta['Descricao'] }}
                                    </div>
                                @endif
                                @if ($conta['Grau'] == '2')
                                    {{ $conta['Descricao'] }}
                                @endif
                                @if ($conta['Grau'] == '3')
                                    {{ $conta['Descricao'] }}
                                @endif
                                @if ($conta['Grau'] == '4')
                                    {{ $conta['Descricao'] }}
                                @endif
                                @if ($conta['Grau'] == '5')
                                    <a href="/Contas/Extrato/{{ $conta['ID'] }}" class="btn btn-link">
                                        {{ $conta['Descricao'] }}
                                    </a>
                                @endif

                            </td>

                            <td style="text-align: right;">
                                {{ number_format($conta['SaldoAtual'], 2, ',', '.') }}
                            </td>

                            <td>
                                <div class="badge bg-success text-wrap" style="width: 100%;">
                                    {{ $conta['Codigo'] }}
                                </div>
                            </td>

                            <td>
                                <div class="badge bg-warning text-wrap" style="width: 100%;">
                                    {{ $conta['Grau'] }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                        <tr>
                            <td>
                                <div class="badge bg-warning text-wrap" style="width: 100%; text-align: right;">
                                    SALDO TOTAL
                                </div>

                                <td>
                                    <div class="badge bg-warning text-wrap" style="width: 100%; text-align: right;">
                                        {{ number_format($somaSaldoAtual, 2, ',', '.') }}
                                    </div>
                                </td>
                            </td>
                        </tr>


                </table>
            @endsection

            @push('scripts')
                <link rel="stylesheet"
                    href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

                <script>
                    $('form').submit(function(e) {
                        e.preventDefault();
                        $.confirm({
                            title: 'Confirmar!',
                            content: 'Confirma a exclusão? Não terá retorno.',
                            buttons: {
                                confirmar: function() {
                                    // $.alert('Confirmar!');
                                    $.confirm({
                                        title: 'Confirmar!',
                                        content: 'Deseja realmente continuar com a exclusão? Não terá retorno.',
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
