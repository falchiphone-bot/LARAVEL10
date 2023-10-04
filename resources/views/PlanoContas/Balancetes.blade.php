@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">

        <div class="card">
            <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                BALANCETE PARA SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
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
                <a class="btn btn-warning" href="/PlanoContas/dashboard">Retornar a lista de opções</a>
            </nav>

            <div class="card-header">
                <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                    <p>BALANCETE
                </div>
            </div>

            <hr>
            <form method="POST" action="/PlanoContas/BalanceteEmpresa/" accept-charset="UTF-8" onsubmit="return validateForm()">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <label for="DataInicial" style="color: black;">Data inicial</label>
                                <input required class="form-control @error('DataInicial') is-invalid @else is-valid @enderror"
                                 name="DataInicial" size="30" type="date" step="1" id="DataInicial" value="{{ $retorno['DataInicial'] ?? null }}">
                            </div>
                            <div class="col-6">
                                <label for="DataFinal" style="color: black;">Data final</label>
                                <input required class="form-control @error('DataFinal') is-invalid @else is-valid @enderror"
                                name="DataFinal" size="30" type="date" step="1" id="DataFinal" value="{{ $retorno['DataFinal'] ?? null }}">
                            </div>

                            <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                <div class="row">
                                    <div class="col-12">
                                        <input type="checkbox" name="Ativo" id="Ativo" value="true">
                                        <label for="ativoCheckbox">Ativo</label>

                                        <input type="checkbox" name="Passivo" id="Passivo" value="true"">
                                        <label for=" passivoCheckbox">Passivo</label>

                                        <input type="checkbox" name="Despesas" id="Despesas" value="true" checked>
                                        <label for="despesasCheckbox">Despesas</label>


                                        <input type="checkbox" name="Receitas" id="Receitas" value="true"">
                                        <label for=" receitasCheckbox">Receitas</label>
                                    </div>


                                    <div class="col-6">

                                        <input type="checkbox" name="tela" id="tela" value="tela" checked>
                                        <label for="visualizarCheckbox">Visualizar na tela</label>
                                        <input type="checkbox" name="MostrarValorRecebido" id="MostrarValorRecebido" value="MostrarValorRecebido" >
                                        <label for="visualizarCheckbox">Mostrar saldos de valores recebidos - resumo</label>
                                    </div>

                                    <div class="col-12">
                                        <input type="radio" name="pdfgerar" id="pdfdownload" value="pdfdownload" >
                                        <label for="pdfCheckbox">Gerar pdf e fazer download do arquivo</label>

                                        <input type="radio" name="pdfgerar" id="pdfvisualizar" value="pdfvisualizar">
                                        <label for="pdfvisualizarCheckbox">Gerar pdf e visualizar primeiro</label>
                                    </div>

                                    <div class="col-12">
                                            <input type="radio" name="Agrupar" id="Descricao" value="Descricao" >
                                            <label for="pdfCheckbox">Gerar e agrupar por descrição</label>

                                            <input type="radio" name="Agrupar" id="Agrupamento" value="Agrupamento">
                                            <label for="pdfvisualizarCheckbox">Gerar e agrupar por agrupamento</label>

                                            <input type="radio" name="Agrupar" id="Nada" value="Nada">
                                            <label for="pdfvisualizarCheckbox">Nenhuma das duas opções anteriores</label>
                                    </div>

                                    <div class="col-12">

                                            <input type="radio" name="Selecao" id="Todas" value="Todas" checked>
                                            <label for="pdfCheckbox">Selecionar todas com saldos sem agrupamento</label>

                                            <input type="radio" name="Selecao" id="Nulos" value="Nulos">
                                            <label for="pdfCheckbox">Selecionar os nulos</label>

                                            <input type="radio" name="Selecao" id="Agrupados" value="Agrupados">
                                            <label for="pdfvisualizarCheckbox">Selecionar os agrupados</label>
                                    </div>
                                    <div class="col-12">
                                                <input type="radio" name="Agrupamentovazio" id="Agrupadosvazio" value="Agrupadosvazio">
                                                <label for="pdfvisualizarCheckbox">Selecionar os agrupados vazios - sem definir nome de agrupamento</label>

                                                <input type="radio" name="Agrupamentovazio" id="Nada" value="Nada">
                                                <label for="pdfvisualizarCheckbox">Nenhuma das duas opções anteriores</label>
                                    </div>


                                </div>
                            </nav>
                        </div>
                    </div>

                    <div class="col-3">
                        <label for="Limite" style="color: black;">Empresas permitidas para o usuário</label>
                        <select required class="form-control select2" id="EmpresaSelecionada" name="EmpresaSelecionada">
                            <option value="{{ $retorno['EmpresaSelecionada'] }}">
                                Selecionar empresa
                            </option>
                            @foreach ($Empresas as $Empresa)
                            <option @if ($retorno['EmpresaSelecionada']==$Empresa->ID) selected @endif
                                value="{{ $Empresa->ID }}">

                                {{ $Empresa->Descricao }}
                            </option>
                            @endforeach
                        </select>
                    </div>



                    <div class="row mt-2">
                        <div class="col-6">
                            <button class="btn btn-primary">Gerar balancete por período</button>

                        </div>
                    </div>

                </div>
            </form>
            <script>
                function validateForm() {
                    // Verificar se pelo menos um checkbox foi selecionado
                    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
                    let isChecked = false;

                    checkboxes.forEach(checkbox => {
                        if (checkbox.checked) {
                            isChecked = true;
                        }
                    });

                    if (!isChecked) {
                        alert("Pelo menos uma opção dos checkboxes deve ser selecionada.");
                        return false; // Impedir o envio do formulário
                    }

                    return true; // Permitir o envio do formulário se pelo menos um checkbox estiver selecionado
                }
            </script>




        </div>
    </div>
    @endsection




    @push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- <script>
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
    </script> -->
    @endpush
