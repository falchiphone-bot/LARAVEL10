@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                    RECEBIMENTOS E REPRESENTANTES PARA SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>
            </div>


            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    {{ session(['success' =>  null ]) }}

                @elseif (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    {{ session(['error' => NULL])}}

                @endif

                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-warning" href="Cadastros">Retornar a lista de opções</a> </nav>


                @can('FORMANDOBASERECEBIMENTOS - INCLUIR')
                    <a href="{{ route('Posicoes.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Incluir recebimento e representantes</a>
                @endcan
                <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                        <p>Total de recebimentos e representantes cadastrados no sistema de gerenciamento administrativo e contábil:
                            {{ $model->count() ?? 0 }}</p>
                    </div>
                </div>

            </div>


            <table class="table" style="background-color: rgb(212, 221, 240);">
                <thead>
                    <tr style="background-color: rgb(150, 180, 220);">
                        <th scope="col" class="px-6 py-4">NOME</th>
                        <th scope="col" class="px-6 py-4">REPRESENTANTE</th>
                        <th scope="col" class="px-6 py-4">DATA</th>
                        <th scope="col" class="px-6 py-4">PATROCÍNIO</th>
                        <th scope="col" class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($model as $Model)
                    <tr>
                        <td class="">{{ $Model->MostraFormandoBase->nome }}</td>
                        <td class="">{{ $Model->MostraRepresentante->nome ?? null }}</td>
                        <td

                            @if ($Model->data)
                                class="">{{ $Model->data->format('d/m/Y') ?? null }}
                            @else
                                class="">{{ $Model->data ?? null }}
                            @endif
                        </td>

                        <td class="">{{ $Model->patrocinio ?? null }}</td>
                        @can('FORMANDOBASERECEBIMENTOS - EDITAR')
                        <td>
                            <a href="{{ route('FormandoBaseRecebimentos.edit', $Model->id) }}" class="btn btn-success" tabindex="-1" role="button" aria-disabled="true">Editar</a>
                        </td>
                        @endcan
                        @can('FORMANDOBASERECEBIMENTOS - VER')
                        <td>
                            <a href="{{ route('FormandoBaseRecebimentos.show', $Model->id) }}" class="btn btn-info" tabindex="-1" role="button" aria-disabled="true">Ver</a>
                        </td>
                        @endcan
                        @can('FORMANDOBASERECEBIMENTOS - EXCLUIR')
                        <td>
                            <form method="POST" action="{{ route('FormandoBaseRecebimentos.destroy', $Model->id) }}">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-danger">Excluir</button>
                            </form>
                        </td>
                        @endcan
                    </tr>
                    @endforeach
                </tbody>
            </table>




                                <div class="badge bg-primary text-wrap" style="width: 100%;">
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
