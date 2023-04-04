@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                        FATURAMENTOS NO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>
                {{-- <a href="{{ route('Faturamentos.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                    aria-disabled="true">Incluir registros</a> --}}

                <div class="row">
                    <div class="card">
                        <div class="card-header">
                            EXIBIÇÃO DO REGISTRO
                        </div>
                        <div class="card-body">
                            <p>
                                Empresa: {{$faturamentos->empresarelacionada->Descricao}}
                                {{-- Empresa {{ $moedasvalores->ValoresComMoeda->nome }} --}}
                            </p>
                            <p>
                                Data: {{ $faturamentos->data->format('d/m/Y')}}
                            </p>
                            <p>
                                Valor do faturamento: {{ $faturamentos->ValorFaturamento }}
                            </p>
                            <p>
                                Valor do imposto: {{ $faturamentos->ValorImposto }}
                            </p>
                            <p>
                                Valor base para lucro líquido: {{ $faturamentos->ValorBaseLucroLiquido }}
                            </p>
                            <p>
                                Percentual para lucro líquido: {{ $faturamentos->PercentualLucroLiquido }}
                            </p>
                            <p>
                                Lucro líquido: {{ $faturamentos->LucroLiquido }}
                            </p>
                            <p>
                                Lançado por: {{ $faturamentos->LancadoPor }}
                            </p>

                        </div>

                        <div class="card-footer">
                            <a href="{{ route('Faturamentos.index') }}">Retornar para a lista</a>
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
