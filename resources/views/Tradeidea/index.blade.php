@extends('layouts.bootstrap5')

@section('content')
<div class="py-5 bg-light">
    <div class="container">
        <div class="card">
            <div class="badge bg-primary text-wrap" style="width: 100%; font-size: 24px; text-align: center;">
                TRADE IDEA
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            {{ session(['success' => null]) }}
            @elseif (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            {{ session(['error' => null])}}
            @endif

            <nav class="navbar navbar-red" style="background-color: hsla(244, 92%, 27%, 0.096);">
                <a class="btn btn-warning" href="{{ route('dashboard') }}">Retornar à lista de opções</a>
            </nav>

            @can('TRADEIDEA - IMPORTAR ARQUIVO EXCEL TRADE IDEA')
            <a href="{{ route('Tradeidea.importarexceltradeidea') }}" class="btn btn-primary btn-lg" tabindex="-1"
                role="button" aria-disabled="true">Importar arquivo excel trade idea</a>
            @endcan

            {{-- @can('PREPARADORES - INCLUIR')
            <a href="{{ route('Preparadores.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                aria-disabled="true">Incluir preparadores/professores/treinadores</a>
            @endcan --}}

        </div>

        <table class="table" style="background-color: rgb(185, 215, 240);">
            <thead>
                <tr>
                    <th scope="col" class="px-6 py-4">CLIENTE</th>
                    <th scope="col" class="px-6 py-4">ASSESSOR</th>
                    <th scope="col" class="px-6 py-4">Id</th>
                    <th scope="col" class="px-6 py-4">TRADEIDEA</th>
                    <th scope="col" class="px-6 py-4">ANALISTA</th>
                    <th scope="col" class="px-6 py-4">VALOR APORTADO</th>
                    <th scope="col" class="px-6 py-4">VALOR LIQUIDADO</th>
                    <th scope="col" class="px-6 py-4">LUCRO/PREJUIZO</th>
                    <th scope="col" class="px-6 py-4">QUANTIDADE</th>
                    <th scope="col" class="px-6 py-4">PRECO ENTRADA</th>
                    <th scope="col" class="px-6 py-4">ENTRADA</th>
                    <th scope="col" class="px-6 py-4">PRECO ENCERRAMENTO</th>
                    <th scope="col" class="px-6 py-4">ENCERRAMENTO</th>
                    <th scope="col" class="px-6 py-4">MOTIVO</th>
                    <th scope="col" class="px-6 py-4"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($model as $Model)
                <tr>
                    <td class="text-end">{{ $Model['cliente'] }}</td>
                    <td class="text-end">{{ $Model['assessor'] }}</td>
                    <td class="text-end">{{ $Model['Id_Tradeidea'] }}</td>
                    <td class="text-end">{{ $Model['tradeidea'] }}</td>
                    <td class="text-end">{{ $Model['analista'] }}</td>



                    <td class="text-end">{{ number_format($Model['valor_aportado'], 2, ',', '.') }}</td>


                    <td class="text-end">{{ number_format($Model['valor_liquidado'], 2, ',', '.') }}</td>


                    <td  class="text-end">{{ number_format($Model['lucro_prejuizo'], 2, ',', '.') }}</td>

                    <td  class="text-end">{{ $Model['quantidade'] }}</td>

                    <td  class="text-end">{{ number_format($Model['preco_entrada'], 2, ',', '.') }}</td>

                    <td class="">{{ $Model['entrada'] }}</td>


                    <td class="text-end">{{ number_format($Model['preco_encerramento'], 2, ',', '.') }}</td>

                    <td class="text-end">{{ $Model['encerramento'] }}</td>
                    <td class="text-end">{{ $Model['motivo'] }}</td>
                    <td>
                        {{-- Add your action buttons here --}}
                    </td>
                </tr>
                @endforeach

                <tr>
                    <td>
                        <form action="{{ route('salvar.tradeidea') }}" method="POST">
                            @csrf
                            <input type="hidden" name="modelo_completo" value="{{ json_encode($model) }}">
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </form>
                    </td>
                 </tr>


            </tbody>
        </table>

        <!-- Add your additional elements here -->

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
                    $.confirm({
                        title: 'Confirmar!',
                        content: 'Deseja realmente continuar?',
                        buttons: {
                            confirmar: function() {
                                e.currentTarget.submit();
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
