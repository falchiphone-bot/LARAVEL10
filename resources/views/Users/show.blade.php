<x-app-layout>
@extends('Layout.Padrao')

{{--}}@section('content')--}}
    <h1 class="text-center">Testes iniciais de Laravel</h1>
    <hr>

    <a href="{{ route('Usuarios.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
        aria-disabled="true">Incluir registros</a>

<div class="row">
    <div class="card">
        <div class="card-header">
            EXIBIÇÃO DO REGISTRO
        </div>
        <div class="card-body">
            <p>
                Nome: {{$cadastro->name}}
            </p>
            <p>
                Email: {{$cadastro->email}}
            </p>
        </div>
        <div class="card-footer">
            <a href="{{route('Usuarios.index')}}">Retornar para a lista</a>
        </div>
    </div>
</div>
{{--}}@endsection --}}
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
