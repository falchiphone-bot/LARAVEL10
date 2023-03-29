@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">
            {{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Empresa</a></li>
              <li class="breadcrumb-item active" aria-current="page">Index</li>
            </ol>
          </nav> --}}

            <div class="card">
                <div class="card-header">
                    Empresas
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

                    <div class="card-header">
                        <p>Total de empresas: {{ $linhas }}</p>
                    </div>
                    <table class="table">
                        <thead>

                            <tr>
                                @can('EMPRESAS - DESBLOQUEAR TODAS')
                                    <th>
                                        <form method="POST" action="{{ route('Empresas.DesbloquearEmpresas') }}"
                                            accept-charset="UTF-8">
                                            <input type="hidden" name="_method" value="PUT">
                                            @include('Empresas.desbloquearempresas')
                                        </form>
                                    </th>
                                @endcan

                                @can('EMPRESAS - BLOQUEAR TODAS')
                                    <th>
                                        <form method="POST" action="{{ route('Empresas.BloquearEmpresas') }}"
                                            accept-charset="UTF-8">
                                            <input type="hidden" name="_method" value="PUT">
                                            @include('Empresas.bloquearempresas')
                                        </form>
                                    </th>
                                @endcan
                            </tr>
                        </thead>

                        <tbody>




                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="px-6 py-4">DESCRIÇÃO</th>
                                        <th scope="col" class="px-6 py-4">CNPJ</th>
                                        <th scope="col" class="px-6 py-4">CRIADO EM</th>
                                        <th scope="col" class="px-6 py-4">BLOQUEIO</th>
                                        <th scope="col" class="px-6 py-4">BLOQUEIO DE DATAS ANTERIORES</th>
                                        <th scope="col" class="px-6 py-4"></th>
                                        <th scope="col" class="px-6 py-4"></th>
                                        <th scope="col" class="px-6 py-4"></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($cadastros as $cadastro)
                                        <tr>
                                           <td class="">
                                                <a href="{{ route('planocontas.autenticar', $cadastro->EmpresaID) }}">
                                                    {{ $cadastro->Descricao }}
                                                </a>
                                            </td>
                                            <td class="">
                                                {{ $cadastro->Cnpj }}
                                            </td>

                                            <td class="">
                                             {{ $cadastro->Created->format('d/m/Y H:i:s')   }}
                                            </td>

                                            <td class="">
                                                @if ($cadastro->Bloqueio)
                                                Sim
                                                @else
                                                Não
                                                @endif
                                            </td>
                                            <td class="">
                                                {{ $cadastro->Bloqueiodataanterior?->format('d/m/Y') }}
                                            </td>

                                            @can('EMPRESAS - EDITAR')
                                                <td>
                                                    <a href="{{ route('Empresas.edit', $cadastro->EmpresaID) }}"
                                                        class="btn btn-success" tabindex="-1" role="button"
                                                        aria-disabled="true">Editar</a>
                                                </td>
                                            @endcan

                                            {{-- <td>
                                <a href="{{ route('Empresas.show', $cadastro->ID) }}" class="btn btn-info"
                                tabindex="-1" role="button" aria-disabled="true">Ver</a>
                                </td>

                                <td>
                                    <form method="POST" action="{{ route('Empresas.destroy', $cadastro->ID) }}">
                                        @csrf
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-danger">
                                        Excluir
                                        </button>
                                    </form>

                                </td> --}}
                                        </tr>
                                    @endforeach
                                </tbody>
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
