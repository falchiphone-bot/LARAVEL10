@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">
            {{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Permissions</a></li>
              <li class="breadcrumb-item active" aria-current="page">edit</li>
            </ol>
          </nav> --}}

            <div class="card">
                <h1 class="text-center">LANCAMENTOS</h1>
                <hr>

                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-warning" href="/Contabilidade">Retornar e ou ir para Contabilidade</a>
                </nav>
                @can('LANCAMENTOS - INCLUIR')
                    <a href="{{ route('PlanoContas.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1"
                        role="button" aria-disabled="true">Incluir contas no plano de contas padrão</a>
                @endcan
                <p>Total de lançamentos: {{ $ultimos_lancamentos->count() ?? 0 }}</p>

                <table class="table table-bordered">

                    <tr>
                        <th>Descrição</th>
                        <th>Tipo</th>
                        <th>Código</th>
                        <th>Grau</th>
                        <th>Bloqueio</th>
                        <th>Bloqueia datas anteriores a</th>
                    </tr>
                    @foreach ($cadastros as $cadastro)
                        <tr>

                            @if ($cadastro->Grau == '1')
                                <td style="padding-left: 10px; Color:red; font-size: 30px;">
                                    {{ $cadastro->Descricao }}
                                </td>
                            @endif


                            @if ($cadastro->Grau == '2')
                                <td style="padding-left: 60px;">
                                    {{ $cadastro->Descricao }}
                                </td>
                            @endif
                            @if ($cadastro->Grau == '3')
                                <td style="padding-left: 90px;">
                                    {{ $cadastro->Descricao }}
                                </td>
                            @endif
                            @if ($cadastro->Grau == '4')
                                <td style="padding-left: 120px;">
                                    {{ $cadastro->Descricao }}
                                </td>
                            @endif
                            @if ($cadastro->Grau == '5')
                                <td style="padding-left: 150px; color:Blue;; font-size: 20px;">
                                    {{ $cadastro->Descricao }}
                                </td>
                            @endif



                            <td>
                                {{ $cadastro->Tipo }}
                            </td>
                            <td>
                                {{ $cadastro->Codigo }}
                            </td>
                            <td>
                                {{ $cadastro->Grau }}
                            </td>
                            <td>
                                {{ $cadastro->Bloqueio }}
                            </td>


                            <td>
                                @php
                                    $Altera = DateTime::createFromFormat('Y-m-d', $cadastro->Bloqueiodataanterior);
                                    if ($Altera instanceof DateTime) {
                                        echo $Altera->format('d-m-Y');
                                    } else {
                                        echo ' ';
                                    }
                                @endphp
                            </td>

                            <td>
                                <div class="row mt-2">
                                    <div class="col-6">
                                        @can('PLANO DE CONTAS - EDITAR')
                                            <a href="{{ route('PlanoContas.edit', $cadastro->ID) }}"
                                                class="btn btn-success btn-sm enabled" tabindex="-1" role="button"
                                                aria-disabled="true">Editar</a>
                                        @endcan

                                        @can('PLANO DE CONTAS - EXCLUIR')
                                            <form method="POST" action="{{ route('PlanoContas.destroy', $cadastro->ID) }}">
                                                @csrf
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button class="btn btn-danger btn-sm enabled" tabindex="-1" role="button"
                                                    aria-disabled="true">Excluir</button>
                                            </form>
                                        @endcan

                                        @can('PLANO DE CONTAS - VER')
                                            <a href="{{ route('PlanoContas.show', $cadastro->ID) }}"
                                                class="btn btn-info btn-sm enabled" tabindex="-1" role="button"
                                                aria-disabled="true">Ver</a>
                                        @endcan
                                    </div>
                                </div>
                            </td>

                        </tr>
                    @endforeach
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
