
@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Usuarios</a></li>
              <li class="breadcrumb-item active" aria-current="page">Index</li>
            </ol>
          </nav>


        <div class="card">
            <div class="card-header">
                Usuários para o sistema administrativo e contábil
            </div>
            <a href="{{ route('Usuarios.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
        aria-disabled="true">Incluir usuário</a>
            <div class="card-body">
                <p>Total de usuários: {{ $linhas}}</p>
                <table class="table">
                    <thead>
                      <tr>
                        <th scope="col" class="px-6 py-4">NOME</th>
                        <th scope="col" class="px-6 py-4">EMAIL
                      </tr>
                    </thead>
                    <tbody>
                    @foreach($cadastros as $cadastro)
                      <tr>

                        <td class="whitespace-nowrap px-6 py-0">  {{ $cadastro->name }}</td>
                        <td class="whitespace-nowrap px-6 py-0">{{ $cadastro->email }}</td>
                        <td>
                            <div class="row mt-2">
                                <div class="col-6">

                                    <a href="{{ route('Usuarios.edit', $cadastro->id) }}" class="btn btn-secondary btn-sm enabled"
                                        tabindex="-1" role="button" aria-disabled="true">Editar</a>

                                    <form method="POST" action="{{ route('Usuarios.destroy', $cadastro->id) }}">
                                        @csrf
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button class="btn btn-danger btn-sm enabled" tabindex="-1" role="button"
                                            aria-disabled="true">Excluir</button>
                                    </form>

                                    <a href="{{ route('Usuarios.show', $cadastro->id) }}" class="btn btn-info btn-sm enabled"
                                        tabindex="-1" role="button" aria-disabled="true">Ver</a>
                                </div>
                            </div>
                        </td>
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
