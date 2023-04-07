@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    DESENVOLVEDOR SICREDI PARA O SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>


                <div class="card-body">
                    @can('COBRANCA - LISTAR')
                      <a href="/Cobranca" class="btn btn-warning">Retornar para opções anteriores</a>
                    @endcan

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @elseif (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif


                    @can('DevSicredi- INCLUIR')
                        <a href="{{ route('DevSicredi.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1"
                            role="button" aria-disabled="true">Incluir nome de DevSicredi</a>
                    @endcan
                    <div class="card-header">
                        <div class="badge bg-success text-wrap" style="width: 100%;">
                        <p>Total de desenvolvedor Sicredi cadastrado no sistema de gerenciamento administrativo e contábil:
                            {{ $DevSicredi->count() ?? 0 }}</p>
                        </div>
                    </div>



                </div>

                <tbody>
                    <table class="table" style="background-color: rgb(247, 247, 213);">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-4">DESENVOLVEDOR</th>
                                <th scope="col" class="px-6 py-4">SICREDI_CLIENT_ID</th>
                                <th scope="col" class="px-6 py-4">SICREDI_CLIENT_SECRET</th>
                                <th scope="col" class="px-6 py-4">SICREDI_TOKEN</th>
                                <th scope="col" class="px-6 py-4">URL_API</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($DevSicredi as $DevSicredi_item)
                                <tr>
                                    <td class="">
                                        {{ $DevSicredi_item->DESENVOLVEDOR }}
                                        </a>
                                    </td>
                                    <td class="">
                                        {{ $DevSicredi_item->SICREDI_CLIENT_ID }}
                                    </td>
                                    <td class="">
                                        {{ $DevSicredi_item->SICREDI_CLIENT_SECRET }}
                                    </td>
                                    <td class="">
                                        {{ $DevSicredi_item->SICREDI_TOKEN }}
                                    </td>
                                    <td class="">
                                        {{ $DevSicredi_item->URL_API }}
                                    </td>


                                    @can('DevSicredi- EDITAR')
                                        <td>
                                            <a href="{{ route('DevSicredi.edit', $DevSicredi_item->id) }}" class="btn btn-success"
                                                tabindex="-1" role="button" aria-disabled="true">Editar</a>
                                        </td>
                                    @endcan

                                    @can('DevSicredi- VER')
                                    <td>
                                        <a href="{{ route('DevSicredi.show', $DevSicredi_item->id) }}" class="btn btn-info"
                                            tabindex="-1" role="button" aria-disabled="true">Ver</a>
                                    </td>
                                    @endcan

                                    @can('DevSicredi- EXCLUIR')
                                    <td>
                                        <form method="POST" action="{{ route('DevSicredi.destroy', $DevSicredi_item->id) }}">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-danger">
                                                Excluir
                                            </button>
                                        </form>
                                    </td>
                                    @endcan
                                </tr>
                            @endforeach

                            {{-- @can('COBRANCA - LISTAR')
                                <tr>
                                    <th>


                                            <a class="btn btn-warning" href="/Cobranca">Opções de obrança</a>


                                    </th>
                                </tr>
                            @endcan
                        </tbody> --}}
                    </table>
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
