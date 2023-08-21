@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                    AVALIAÇÃO DOS FORMANDOS/ATLETAS
                </div>
            </div>


            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @elseif (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-warning" href="/dashboard">Retornar a lista de opções</a> </nav>


                <!-- @can('FERIADOS- INCLUIR')
                    <a href="{{ route('Feriados.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Incluir feriado</a>
                @endcan -->
                <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                        <p>Total de avaliações cadastrados:
                            {{ $model->count() ?? 0 }}</p>
                    </div>
                </div>



            </div>


           <div class="card bg-success text-white">
            <form method="GET" action="{{ route('FormandoBaseAvaliacao.index') }}">
            <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="sort" id="sort-by-new" value="datenew">
        <label class="form-check-label" for="sort-by-new">Ordem de data decrescente - últimos inseridos</label>
    </div>

    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="sort" id="sort-by-date" value="date">
        <label class="form-check-label" for="sort-by-date">Ordem de data crescente</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="sort" id="sort-by-score" value="score">
        <label class="form-check-label" for="sort-by-score">Ordem de notas</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="sort" id="sort-by-name" value="name">
        <label class="form-check-label" for="sort-by-name">Ordem de nome</label>
    </div>
    <button type="submit" class="btn btn-primary">Executar a escolha para visualizar relatório</button>
</form>
</div>





            <tbody>
                <table class="table" style="background-color: rgb(247, 247, 213);">
                    <thead>
                        <tr>
                            <th scope="col" class="px-6 py-4">DATA</th>
                            <th scope="col" class="px-6 py-4">NOTA</th>
                            <th scope="col" class="px-6 py-4">NOME</th>
                            <th scope="col" class="px-6 py-4"></th>
                            <th scope="col" class="px-6 py-4"></th>
                            <th scope="col" class="px-6 py-4"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($model as $Model)
                            <tr>


   <td>{{ \Carbon\Carbon::parse($Model->created_at)->format('d/m/Y H:i:s') }}</td>

   <td> {{  number_format($Model->avaliacao,2) }} </td>


                                <td class="">
                                    {{ $Model->MostraFormando->nome }}
                                </td>



                                <!-- @can('FERIADOS- EDITAR')
                                    <td>
                                        <a href="{{ route('Feriados.edit', $Model->id) }}" class="btn btn-success" tabindex="-1"
                                            role="button" aria-disabled="true">Editar</a>
                                    </td>
                                @endcan

                                @can('FERIADOS- VER')
                                    <td>
                                        <a href="{{ route('Feriados.show', $Model->id) }}" class="btn btn-info" tabindex="-1"
                                            role="button" aria-disabled="true">Ver</a>
                                    </td>
                                @endcan

                                @can('FERIADOS- EXCLUIR')
                                    <td>
                                        <form method="POST" action="{{ route('Feriados.destroy', $Model->id) }}">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-danger">
                                                Excluir
                                            </button>
                                        </form>
                                    </td>
                                @endcan
                            </tr> -->
                        @endforeach
                    </tbody>
                </table>
                                <div class="badge bg-primary text-wrap" style="width: 100%;">
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
