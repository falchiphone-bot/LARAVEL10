

<x-app-layout>

@section('content')

  @extends('Layout.Padrao')
   <h1 class="text-center">Funções</h1>
    <hr>
       <a href="{{ route('Funcoes.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
        aria-disabled="true">Incluir funções</a>

        <table class="table table-dark"  >

            <tbody>

              <tr>
                <td >NOME</td>
                <td >GUARDA</td>
                <td></td>
              </tr>


        @foreach ($cadastros as $cadastro)
            <tr>
                <td>
                    {{ $cadastro->name }}
                </td>
                <td>
                    {{ $cadastro->guard_name }}
                </td>


                <td>


                    @php
                        $Altera = DateTime::createFromFormat("Y-m-d", $cadastro->created_at);
                        if ($Altera instanceof DateTime) {
                            echo $Altera->format('d-m-Y');
                        } else {
                            echo " ";
                        }
                    @endphp
                </td>

                <td>
                    <div class="row mt-2">
                        <div class="col-6">

                            <a href="{{ route('Funcoes.edit', $cadastro->id) }}" class="btn btn-secondary btn-sm enabled"
                                tabindex="-1" role="button" aria-disabled="true">Editar</a>
                            <form method="POST" action="{{ route('Funcoes.destroy', $cadastro->id) }}">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <button class="btn btn-danger btn-sm enabled" tabindex="-1" role="button"
                                    aria-disabled="true">Excluir</button>
                            </form>

                            <a href="{{ route('Funcoes.show', $cadastro->id) }}" class="btn btn-info btn-sm enabled"
                                tabindex="-1" role="button" aria-disabled="true">Ver</a>
                        </div>
                    </div>
                </td>

            </tr>
        @endforeach
    </table>
    </tbody>

@endsection
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

