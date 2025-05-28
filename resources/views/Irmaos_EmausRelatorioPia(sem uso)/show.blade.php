@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    TÓPICO PARA O PIA DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>
                <a href="{{ route('Irmaos_EmausServicos.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                    aria-disabled="true">Incluir SERVIÇOS</a>

                <div class="row">
                    <div class="card">
                        <div class="card-header">
                            EXIBIÇÃO DO REGISTRO DE TÓPICO PARA O PIA
                        </div>
                        <div class="card-body">
                            <p>
                                NOME: {{ $cadastro->nomePia }}
                            </p>


                        </div>
                        <div class="card-footer">
                            <a href="{{ route('Irmaos_EmausPia.index') }}">Retornar para a lista</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
