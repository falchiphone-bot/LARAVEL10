@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                    EXPORTAR ARQUIVO EXCEL O EXTRATO DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
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
                    {{ session(['error' => null]) }}
                @endif

                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-warning" href="/Contabilidade">Retornar a lista de opções</a>
                </nav>

                <div class="card-header">
                    <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px">
                        <p>EXPORTAÇÃO DE ARQUIVO XLSX
                    </div>
                </div>

                <hr>
                <form method="POST" action="/Lancamentos/ExportarExtratoExcelpost" accept-charset="UTF-8">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <label for="DataInicial" style="color: black;">Data inicial</label>
                                    <input required
                                        class="form-control @error('DataInicial') is-invalid @else is-valid @enderror"
                                        name="DataInicial" size="30" type="date" step="1" id="DataInicial"
                                        value="{{ $retorno['DataInicial'] ?? null }}">
                                </div>
                                <div class="col-6">
                                    <label for="DataFinal" style="color: black;">Data final</label>
                                    <input required
                                        class="form-control @error('DataFinal') is-invalid @else is-valid @enderror"
                                        name="DataFinal" size="30" type="date" step="1" id="DataFinal"
                                        value="{{ $retorno['DataFinal'] ?? null }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-3">
                            <label for="Limite" style="color: black;">Empresas permitidas para o usuário</label>
                            <select required class="form-control select2" id="EmpresaSelecionada" name="EmpresaSelecionada">
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
                        <div class="col-3">
                            <label for="Limite" style="color: red;">Contas</label>
                            <select required class="form-control select2" id="ContaSelecionada" name="ContaSelecionada">
                                <option value="">
                                    Selecionar conta
                                </option>
                                @foreach ($Contas as $Conta)
                                    <option @if ($retorno['ContaSelecionada'] == $Conta->ID) selected @endif
                                        value="{{ $Conta->ID }}">

                                        {{ $Conta->Descricao  . " - " . $Conta  }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div class="row mt-2">
                            <div class="col-6">
                                <br>
                                <button class="btn btn-primary">Gerar arquivo exportação da conta com empresa selecionada</button>
                            </div>
                        </div>

                    </div>
                </form>
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
