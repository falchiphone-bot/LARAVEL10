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
                <h1 class="text-center">Contas</h1>
                <hr>
                {{-- @cannot('PLANO DE CONTAS - LISTAR')
                    <li>
                        <a href="/dashboard" data-bs-toggle="tooltip" data-bs-placement="center"
                            data-bs-custom-class="custom-tooltip" data-bs-title="Clique e vá para o início do sistema"
                            class="botton-link text-black">
                            <i class="fa-solid fa-house"></i>
                            <a href="{{ route('dashboard') }}" class="btn btn-danger btn-lg enabled" tabindex="-1"
                                role="button" aria-disabled="true">SEM PERMISSÃO PARA ESTE SERVIÇO. CONSULTE O ADMINISTRADOR.
                                Clique e vá para o início do sistema</a>
                        </a>
                    </li>
                @endcan --}}


                {{-- @can('PLANO DE CONTAS - INCLUIR')
                    <a href="{{ route('PlanoContas.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1"
                        role="button" aria-disabled="true">Incluir contas no plano de contas padrão</a>
                @endcan --}}
                {{-- <p>Total de contas: {{ $linhas }}</p> --}}

                <table class="table table-bordered">

                    <tr>
                        <th>Descrição</th>
                    </tr>
                    @foreach ($contasEmpresa as $conta)
                        <tr>
                            @if ($conta->PlanoConta->Grau == '1')
                                <td style="padding-left: 10px; Color:red; font-size: 30px;">
                                    {{ $conta->PlanoConta->Descricao }}
                                </td>
                            @endif

                            @if ($conta->PlanoConta->Grau == '2')
                                <td style="padding-left: 60px;">
                                    {{ $conta->PlanoConta->Descricao }}
                                </td>
                            @endif
                            @if ($conta->PlanoConta->Grau == '3')
                                <td style="padding-left: 90px;">
                                    {{ $conta->PlanoConta->Descricao }}
                                </td>
                            @endif
                            @if ($conta->PlanoConta->Grau == '4')
                                <td style="padding-left: 120px;">
                                    {{ $conta->PlanoConta->Descricao }}
                                </td>
                            @endif
                            @if ($conta->PlanoConta->Grau == '5')
                                <td style="padding-left: 150px; color:Blue;; font-size: 20px;">
                                    {{ $conta->PlanoConta->Descricao }}
                                </td>
                            @endif


{{--
                            <td>
                                {{ $conta->Tipo }}
                            </td>
                            <td>
                                {{ $conta->Codigo }}
                            </td>
                            <td>
                                {{ $conta->Grau }}
                            </td>
                            <td>
                                {{ $conta->Bloqueio }}
                            </td>


                            <td>
                                @php
                                    $Altera = DateTime::createFromFormat('Y-m-d', $conta->Bloqueiodataanterior);
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
                                            <a href="{{ route('PlanoContas.edit', $conta->ID) }}"
                                                class="btn btn-success btn-sm enabled" tabindex="-1" role="button"
                                                aria-disabled="true">Editar</a>
                                        @endcan

                                        @can('PLANO DE CONTAS - EXCLUIR')
                                            <form method="POST" action="{{ route('PlanoContas.destroy', $conta->ID) }}">
                                                @csrf
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button class="btn btn-danger btn-sm enabled" tabindex="-1" role="button"
                                                    aria-disabled="true">Excluir</button>
                                            </form>
                                        @endcan

                                        @can('PLANO DE CONTAS - VER')
                                            <a href="{{ route('PlanoContas.show', $conta->ID) }}"
                                                class="btn btn-info btn-sm enabled" tabindex="-1" role="button"
                                                aria-disabled="true">Ver</a>
                                        @endcan
                                    </div>
                                </div>
                            </td> --}}

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
