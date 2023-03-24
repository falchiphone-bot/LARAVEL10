@extends('layouts.bootstrap5')
@section('content')
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <section>
                            <header>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Permissão para : {{$cadastro->name}}
                                </h2>
                            </header>





                            <form method="post" action="/Funcoes/salvarpermissao/{{$cadastro->id}}" class="mt-6 space-y-6">
                                @csrf
                                <div class="col-span-12 sm:col-span-12">
                                    <label for="permissao" class="block text-sm font-medium leading-6 text-gray-900"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Permissões</font></font></label>
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
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                        Salvar
                                    </button>
                                </div>
                            </form>
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
