@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
          {{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Permissions</a></li>
              <li class="breadcrumb-item active" aria-current="page">edit</li>
            </ol>
          </nav> --}}
            <h1 class="text-center">Plano de contas padrão para contabilidade</h1>
        <div class="card">


            @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
             {{ session(['success' => NULL])}}
        @elseif (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            {{ session(['error' => NULL])}}
        @endif



            <h1 class="text-center">Edição da conta</h1>
            <hr>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{route('PlanoContas.update',$cadastro->ID)}}" accept-charset="UTF-8">
                <input type="hidden" name="_method" value="PUT">
                @include('PlanoContas.campos')
            </form>





            <form method="POST" action="/PlanoContas" accept-charset="UTF-8">
                @include('PlanoContas.camposincluir')
            </form>

      <form method="POST" action="{{route('PlanoContas.update',$cadastro->ID)}}" accept-charset="UTF-8">
            <input type="hidden" name="_method" value="PUT">
                     @include('PlanoContas.incluirconta')
            </form>

        </div>
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
