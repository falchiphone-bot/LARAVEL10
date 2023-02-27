@extends('Layout.Padrao')

@section('content')
    <h1 class="text-center">Testes iniciais de Laravel</h1>
    <hr>

    <a href="{{ route('Testes.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
        aria-disabled="true">Incluir registros</a>

    <table class="table">
        <tr>
            <th>Nome</th>
            <th>Email</th>
        </tr>
        @foreach ($cadastros as $cadastro)
            <tr>
                <td>
                    {{ $cadastro->nome }}
                </td>
                <td>
                    {{ $cadastro->email }}
                </td>
                <td>
                    <div class="row mt-2">
                        <div class="col-6">

                            <a href="{{ route('Testes.edit', $cadastro->id) }}" class="btn btn-secondary btn-sm enabled"
                                tabindex="-1" role="button" aria-disabled="true">Editar</a>
                            <form method="POST" action="{{ route('Testes.destroy', $cadastro->id) }}">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <button class="btn btn-danger btn-sm enabled" tabindex="-1" role="button"
                                    aria-disabled="true">Excluir</button>
                            </form>

                            <a href="{{ route('Testes.show', $cadastro->id) }}" class="btn btn-info btn-sm enabled"
                                tabindex="-1" role="button" aria-disabled="true">Ver</a>
                        </div>
                    </div>
                </td>

            </tr>
        @endforeach
    </table>
@endsection

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
