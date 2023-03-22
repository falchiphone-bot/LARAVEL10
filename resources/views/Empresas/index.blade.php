
{{-- @extends('Layout.Padrao') --}}
<x-app-layout>
@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="p-4 sm:p-8 bg-gray-700 dark:bg-gray-800 shadow sm:rounded-lg">

                <section>
                    <header>
                        <div class="leading-12 text-center
                            text-white-500 align-baseline
                            bg-red-200 w-1/2 h-1/3 p-5 my-10
                            border-t-2
                            border-solid
                            border-green-900
                            opacity-40
                            shadow-2xl">
                            CADASTRO DE EMPRESAS
                        </div>
                            <p>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                        <a href="{{ route('Empresas.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                                    aria-disabled="true">Incluir empresas</a>
                                </button>
                            </p>
                            <div class="leading-12 text-center
                            text-white-500 align-baseline
                            bg-red-500 w-1/2 h-1/3 p-5 my-10
                            border-t-2
                            border-solid
                            border-green-900
                            opacity-40
                            shadow-2xl">
                                  Registro de empresas registradas para contabilidade: {{ $linhas}}
                            </div>

                </section>





        <div class="flex flex-col">
            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
              <div class="inline-block min-w-full py-2 sm:px-6 lg:px-8">
                <div class="overflow-hidden">
                  <table class="min-w-full text-left text-sm font-light">
                  
                    <thead
                      {{-- class="border-b bg-black font-medium dark:border-neutral-500 dark:bg-neutral-600"> --}}
                      class="border-b bg-neutral-800 font-medium text-red-500 dark:border-neutral-500 dark:bg-neutral-900">

                      <tr>

                        <th scope="col" class="px-6 py-4">DESCRIÇÃO</th>
                        <th scope="col" class="px-6 py-4">CNPJ</th>
                        <th scope="col" class="px-6 py-4">BLOQUEIO</th>
                        <th scope="col" class="px-6 py-4">BLOQUEIO DE DATAS ANTERIORES</th>
                        <th scope="col" class="px-6 py-4"></th>
                        <th scope="col" class="px-6 py-4"></th>
                        <th scope="col" class="px-6 py-4"></th>
                      </tr>
                    </thead>

                    @foreach($cadastros as $cadastro)
                </td>
                      <tr
                        class="border-b bg-grain-300 dark:border-neutral-500 dark:bg-neutral-700">

                        <td class="whitespace-nowrap px-6 py-0">  {{ $cadastro->Descricao }}</td>
                        <td class="whitespace-nowrap px-6 py-0">{{ $cadastro->Cnpj }}</td>
                        <td class="whitespace-nowrap px-6 py-0"> {{ $cadastro->Bloqueio }}</td>
                        <td class="whitespace-nowrap px-6 py-0">
                                <?
                                $Altera = DateTime::createFromFormat("Y-m-d", $cadastro->Bloqueiodataanterior);
                                if ($Altera instanceof DateTime) {
                                    echo $Altera->format('d-m-Y');
                                } else {
                                    echo " ";
                                }
                            ?>
                        </td>

                                 <td>
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-500 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-green-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">


                                            <a href="{{ route('Empresas.edit', $cadastro->ID) }}" class="btn btn-secondary btn-sm enabled"
                                            tabindex="-1" role="button" aria-disabled="true">Editar</a>
                                    </button>
                                </td>
                            </td>
                            <td>

                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                <a href="{{ route('Empresas.show', $cadastro->ID) }}" class="btn btn-info btn-sm enabled"
                                tabindex="-1" role="button" aria-disabled="true">Ver</a>
                            </button>
                        </td>
                                <td>
                                    <form method="POST" action="{{ route('Empresas.destroy', $cadastro->ID) }}">
                                        @csrf
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                        Excluir
                                        </button>
                                        </form>
                                    </button>
                                </td>


                        </div>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>



  </tbody>


        </div>
        </div>
    </body>
</html>


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
    </x-app-layout>

