@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">


            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    EMPRESAS DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
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
                        <div class="d-flex flex-wrap align-items-end gap-2 justify-content-between">
                            <div>
                                <p class="mb-2">
                                    Total de empresas cadastradas no sistema de gerenciamento administrativo e contábil:
                                    {{ $linhas }}
                                    @if(!empty($q))
                                        <small class="text-muted">(filtrado por "{{ $q }}")</small>
                                    @endif
                                </p>
                                @can('EMPRESAS - INCLUIR')
                                    <a href="{{ route('Empresas.create') }}" class="btn btn-primary btn-sm" tabindex="-1" role="button"
                                        aria-disabled="true">Incluir empresa e depois ir em Usuários e dar permissão para aparecer na lista de empresas</a>
                                @endcan
                            </div>
                            <form method="GET" action="{{ route('Empresas.index') }}" class="row row-cols-lg-auto g-2 align-items-end">
                                <div class="col-12">
                                    <label for="q" class="form-label mb-0">Buscar por nome</label>
                                    <input type="text" class="form-control" id="q" name="q" value="{{ $q ?? '' }}" placeholder="Ex.: Falchi"/>
                                </div>
                                <div class="col-12">
                                    <label for="per_page" class="form-label mb-0">Por página</label>
                                    <select class="form-select" id="per_page" name="per_page">
                                        @foreach($allowedPerPage as $size)
                                            <option value="{{ $size }}" @selected(($perPage ?? 15)==$size)>{{ $size }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 d-flex gap-2">
                                    <button type="submit" class="btn btn-secondary">Buscar</button>
                                    @if(!empty($q) || ($perPage ?? 15) != 15)
                                        <a href="{{ route('Empresas.index') }}" class="btn btn-outline-secondary">Limpar</a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>


                    <table class="table" style="background-color: yellow;">

                        <thead>



                            <tr>
                                @can('EMPRESAS - DESBLOQUEAR TODAS')
                                    <th>
                                        <form method="POST" action="{{ route('Empresas.DesbloquearEmpresas') }}" class="js-confirm"
                                            accept-charset="UTF-8">
                                            <input type="hidden" name="_method" value="PUT">
                                            @include('Empresas.desbloquearempresas')
                                        </form>
                                    </th>
                                @endcan

                                @can('EMPRESAS - BLOQUEAR TODAS')
                                    <th>
                                        <form method="POST" action="{{ route('Empresas.BloquearEmpresas') }}" class="js-confirm"
                                            accept-charset="UTF-8">
                                            <input type="hidden" name="_method" value="PUT">
                                            @include('Empresas.BloquearEmpresas')
                                        </form>
                                    </th>
                                @endcan
                            </tr>
                        </thead>

            </div>

            <tbody>




                <table class="table" style="background-color: rgb(247, 247, 213);">
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
                        @forelse ($cadastros as $cadastro)
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
                                    {{ $cadastro->Created->format('d/m/Y H:i:s') }}
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
                                        <a href="{{ route('Empresas.edit', $cadastro->EmpresaID) }}" class="btn btn-success"
                                            tabindex="-1" role="button" aria-disabled="true">Editar</a>
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
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Nenhuma empresa encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-end">
                    {{ $cadastros->links() }}
                </div>
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

        // Confirmação apenas para forms com a classe .js-confirm
        $('form.js-confirm').on('submit', function(e) {
            e.preventDefault();
            const form = this;
            $.confirm({
                title: 'Confirmar!',
                content: 'Deseja realmente continuar?',
                buttons: {
                    confirmar: function() {
                        form.submit();
                    },
                    cancelar: function() {}
                }
            });
        });
    </script>
@endpush
