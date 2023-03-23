@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Users</a></li>
              <li class="breadcrumb-item active" aria-current="page">show</li>
            </ol>
          </nav>

        <div class="card">


        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <section>
                            <header>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Permissões para : {{$cadastro->name}}
                                </h2>

                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Email : {{$cadastro->email}}
                                </p>
                            </header>
                            <header>
                                <div class="card">
                                <a class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                                aria-disabled="true">Permissões</a>
                            </header>

                            <div class="card">
                            <form method="post" action="/Usuarios/salvarpermissao/{{$cadastro->id}}" class="mt-6 space-y-6">
                                @csrf
                                <div class="col-span-12 sm:col-span-12">
                                        
                                    <select multiple id="permissao" name="permissao[]" autocomplete="permissao-name"
                                            class="select2 mt-2 block w-full rounded-md border-0 bg-white py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2
                                            focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">


                                        @foreach($permissoes as $id=>$name)
                                                <option
                                                    @if($cadastro->hasPermissionTo($name))
                                                            selected
                                                    @endif
                                                value={{$id}}><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">{{$name}}</font></font>
                                            </option>

                                            @endforeach
                                    </select>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-4">
                                        <button type="submite" class="btn btn-success">Salvar</button>
                                </div>
                                </div>
                            </div>

                            </form>

                        </section>
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <section>
                            <header>
                                <div class="card">
                                <a class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                                aria-disabled="true">Função</a>
                            </header>

                            <form method="post" action="/Usuarios/salvarfuncao/{{$cadastro->id}}" class="mt-6 space-y-6">
                                @csrf
                                <div class="col-span-12 sm:col-span-12">


                                    <select multiple id="funcao" name="funcao[]" autocomplete="funcao-name"
                                            class="select2 mt-2 block w-full rounded-md border-0 bg-gray py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2
                                            focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">


                                        @foreach($funcoes as $id => $name)
                                                <option
                                                    @if($cadastro->hasRole($name))
                                                            selected
                                                    @endif
                                                value={{$id}}><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">{{$name}}</font></font>
                                            </option>

                                            @endforeach
                                    </select>
                                </div>

                                <div class="flex items-center gap-4">
                                        <button type="submite" class="btn btn-success">Salvar</button>
                                </div>
                            </form>
                        </div>
                        </section>
                    </div>
                </div>

               {{-- para lembrar de algo.... pode apagar no futuro - 20.03.2022 - 13:02

               <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <section>
                            <header>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Permissões habilitadas para o usuário
                                </h2>
                            </header>

                            <!-- component -->
                            <div class="flex flex-col">
                                <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                                    <div class="py-2 inline-block min-w-full sm:px-6 lg:px-8">
                                        <div class="overflow-hidden">
                                            <table class="min-w-full text-center">
                                                <thead class="border-b">
                                                <tr>
                                                    <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4">
                                                        Nome
                                                    </th>
                                                    <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4">
                                                        Desabilitar
                                                    </th>

                                                </tr>
                                                </thead>
                                                <tbody>
                                              @foreach($cadastro->getAllPermissions() as $permissao)

                                                  <form id="RevogarPermissao" method="Post" action="/Usuarios/revogarpermissao/{{$cadastro->id}}">
                                                      @csrf
                                                      <tr class="border-b">
                                                          <td class="text-sm text-gray-900 font-medium px-6 py-4 whitespace-nowrap">
                                                              {{$permissao->name}}
                                                          </td>
                                                          <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                                              <button name ="RevogaPermissao" type="submit"value="{{$permissao->name}}" class="inline-flex items-center px-4 py-2 bg-gray-800 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                                  Excluir
                                                              </button>
                                                          </td>

                                                      </tr>
                                                  </form>

                                              @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </section>
                    </div>
                </div>


            </div>
        </div>
        --}}


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

