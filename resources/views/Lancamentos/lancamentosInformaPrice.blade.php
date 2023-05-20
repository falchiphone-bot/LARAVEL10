@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                @if (session('Lancamento'))
                    <div class="alert alert-danger">
                        {{ session('Lancamento') }}
                    </div>
                    {{ session(['Lancamento' => null]) }}
                @endif

                <div class="badge bg-secondary text-wrap" style="width: 100%;
                ;font-size: 24px; lign=˜Center˜">
                    INFORMAR VALORES PARA CALCULAR TABELA PRICE E LANÇAR NO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>


                <div class="card-body">

                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                        <a class="btn btn-warning" href="\Contabilidade">Retornar a lista de opções</a>

                    </nav>
                </div>

                <div class="badge bg-warning text-wrap"
                    style="width: 100%; font-size: 24px; color: black; text-align: center;">
                    <div class="card">
                        <nav class="navbar navbar-success" style="background-color: hsla(234, 92%, 47%, 0.096);">
                            PREENCHER OS CAMPOS ABAIXO
                        </nav>
                    </div>

                </div>
                <div class="card-body">
                    <div class="row">

                        <form method="POST" action="/lancamentos/lancamentotabelaprice" enctype="multipart/form-data">
                            @csrf


                            <div class="badge bg-primary text-wrap"
                            style="width: 100%;
            ;font-size: 24px; lign=˜Center˜">
                        <label for="Parcelas">Quantidade de parcelas</label>
                            <div class="col-sm-2">
                                <input required
                                    class="form-control @error('Parcelas') is-invalid @else is-valid @enderror"
                                    name="Parcelas" type="number"  id="Parcelas"
                                    value=" ">
                                @error('Parcelas')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                            <div class="badge bg-success text-wrap"
                            style="width: 100%;
            ;font-size: 24px; lign=˜Center˜">
                        <label for="TotalFinanciado">Valor do capital para cálculo</label>
                            <div class="col-sm-2">
                                <input required
                                    class="form-control money @error('TotalFinanciado') is-invalid @else is-valid @enderror"
                                    name="TotalFinanciado" type="decimal" step="0.01" id="TotalFinanciado"
                                    value=" ">
                                @error('TotalFinanciado')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                            <div class="badge bg-danger text-wrap"
                                style="width: 100%;
                ;font-size: 24px; lign=˜Center˜">
                            <label for="TaxaJurosMensal">Taxa de juros para cálculo: Exemplo: X.XXXX(4 dígitos após o
                                                                    ponto separação
                                                                    decimal)</label>
                                <div class="col-sm-2">
                                    <input required
                                        class="form-control moneyjuros @error('TaxaJurosMensal') is-invalid @else is-valid @enderror"
                                        name="TaxaJurosMensal" type="decimal" step="0.01" id="TaxaJurosMensal"
                                        value=" ">
                                    @error('TaxaJurosMensal')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="badge bg-success text-wrap"
                            style="width: 100%;
            ;font-size: 24px; lign=˜Center˜">
                        <label for="TotalFinanciado">Data início dos lançamentos</label>
                            <div class="col-sm-2">
                                <input required
                                    class="form-control @error('TotalFinanciado') is-invalid @else is-valid @enderror"
                                    name="DataInicio" type="date" id="DataInicio"
                                    value=" ">
                            </div>

                                    <div class="col-9">
                                        <label for="Limite" style="color: white;">Empresas permitidas para o usuário</label>
                                        <select class="form-control select2" id="EmpresaSelecionada" name="EmpresaSelecionada">
                                            <option value="">
                                                Selecionar empresa
                                            </option>
                                            @foreach ($Empresas as $Empresa)
                                                <option @if ($retorno['EmpresaSelecionada'] == $Empresa->ID) selected @endif
                                                    value="{{ $Empresa->ID }}">

                                                    {{ $Empresa->Descricao }}
                                                </option>
                                            @endforeach


                                        </select>
                                    </div>


                    <input type="checkbox" name="VerVariaveis" value="1">
                    <label for="checkbox_enviar">Ver os valores em tela debug</label>
                    <br>
                    <p class="my-2">
                        <button type="submit" class="btn btn-secondary">Enviar calcular e efetuar lançamentos</button>
                    </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.js"></script>
    <script>
        $(document).ready(function() {
            $('.money').mask('000,000,000,000,000.00', {
                reverse: true
            });
            $('.moneyjuros').mask('000.0000', {
                reverse: true
            });
        });
    </script>
@endpush
