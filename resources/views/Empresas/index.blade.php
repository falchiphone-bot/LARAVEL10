
{{-- @extends('Layout.Padrao') --}}
<x-app-layout>
@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="max-w-xl">
                <section>
                    <header>
                        <h1 class="text-lg font-medium text-gray-900 dark:text-green-400">
                            CADASTRO DE EMPRESAS
                            <p>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                        <a href="{{ route('Empresas.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                                    aria-disabled="true">Incluir empresas</a>
                                </button>
                            </p>

                                 <p>Total de empresas: {{ $linhas}}</p>


        {{-- <table class="min-w-full text-left"> --}}
            {{-- <table class="border-separate border-spacing-4 border border-slate-800 ..."> --}}
                <table class="hover:table-fixed">
                <tr>

                    <th class="border border-slate-400">
                        DESCRIÇÃO
                    </th>

                        <th class="border border-slate-300">
                        CNPJ
                    </th>

                        <th class="border border-slate-300">
                        BLOQUEIO
                    </th>

                        <th class="border border-slate-300">
                        BLOQUEIA DATAS ANTERIORES A
                    </th>
                </tr>
            </thead>

                <tbody>
                @foreach($cadastros as $cadastro)
                    <tr>

                       <td class="border border-slate-400 ...">
                            {{ $cadastro->Descricao }}
                        </td>
                        <td class="border border-slate-300 ...">
                            {{ $cadastro->Cnpj }}
                        </td>
                        <td class="border border-slate-300 ...">
                            {{ $cadastro->Bloqueio }}
                        </td>

                        <td class="border border-slate-300 ...">
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
                            <button type="button" class="cursor-pointer ...">
                                Submit
                              </button>
                              <button type="button" class="cursor-progress ...">
                                Saving...
                              </button>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">


                                            <a href="{{ route('Empresas.edit', $cadastro->ID) }}" class="btn btn-secondary btn-sm enabled"
                                            tabindex="-1" role="button" aria-disabled="true">Editar</a>
                                    </button>



                                    <form method="POST" action="{{ route('Empresas.destroy', $cadastro->ID) }}">
                                        @csrf
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                        Excluir
                                        </button>
                                        </form>
                                    </button>


                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                        <a href="{{ route('Empresas.show', $cadastro->ID) }}" class="btn btn-info btn-sm enabled"
                                        tabindex="-1" role="button" aria-disabled="true">Ver</a>
                                    </button>

                                </div>
                            </div>
                        </td>
                    </tr>


                @endforeach

        </table>
  </tbody>
 </section>
            </div>
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
    </x-app-layout>

