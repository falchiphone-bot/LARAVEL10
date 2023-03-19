
<x-app-layout>
@extends('Layout.Padrao')
{{-- @section('content') --}}
    <h1 class="text-center">Funções tem Permissões</h1>
    <hr>

    <a href="{{ route('TemFuncoes.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
        aria-disabled="true">Incluir função com permissões</a>

    <table class="table">
        <tr>
            <th>Permissão</th>
            <th>Funcão</th>

        </tr>
        @foreach ($cadastros as $cadastro)
            <tr>
                <td>
                    {{ $cadastro->permission_id }}
                </td>
                <td>
                    {{ $cadastro->role_id }}
                </td>

                <td>
                    <div class="row mt-2">
                        <div class="col-6">

                            <a href="{{ route('TemPermissoes.edit', $cadastro->permission_id) }}" class="btn btn-secondary btn-sm enabled"
                               tabindex="-1" role="button" aria-disabled="true">Editar</a>

                            <a href="{{ route('TemPermissoes.edit', $cadastro->permission_id) }}" class="btn btn-info btn-sm enabled"
                               tabindex="-1" role="button" aria-disabled="true">2-Editar</a>

                            <form method="POST" action="{{ route('TemPermissoes.destroy', $cadastro->permission_id) }}">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <button class="btn btn-danger btn-sm enabled" tabindex="-1" role="button"
                                        aria-disabled="true">Excluir</button>
                            </form>

                            <a href="{{ route('TemFuncoes.show', $cadastro->permission_id) }}" class="btn btn-info btn-sm enabled"
                               tabindex="-1" role="button" aria-disabled="true">Ver</a>
                        </div>
                    </div>
                </td>

            </tr>
        @endforeach
    </table>
{{-- @endsection --}}
</x-app-layout>
@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
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

