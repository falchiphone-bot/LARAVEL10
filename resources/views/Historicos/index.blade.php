@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;
                ;font-size: 24px; lign=˜Center˜">
                    HISTÓRICOS DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
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
                        <a class="btn btn-warning" href="/Contabilidade">Retornar e ou ir para Contabilidade</a>
                    </nav>

                    @can('HISTORICO - INCLUIR')
                        <a href="{{ route('Historicos.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1"
                            role="button" aria-disabled="true">Incluir nome de históricos</a>
                    @endcan





                </div>



                <form method="POST" action="{{ route('pesquisapost') }}" accept-charset="UTF-8">
                    @csrf
                    <div class="card">
                        <div class="card-body" style="background-color: rgb(33, 244, 33)">
                            <div class="row">
                                <div class="col-3">
                                    <label for="EmpresaSelecionada" style="color: black;">Empresas permitidas para o usuário</label>
                                    <select class="form-control select2" id="EmpresaSelecionada" name="EmpresaSelecionada">
                                        <option value="">
                                            Selecionar empresa
                                        </option>
                                        @foreach ($Empresas as $Empresa)
                                            <option selected value="{{ $Empresa->ID }}">

                                                {{ $Empresa->Descricao }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-3">
                                    <label for="Pesquisa" style="color: black;">Pesquisar texto no histórico</label>
                                    <input type="text" name='PesquisaTexto' class="form-control">
                                </div>
                                <div class="row mt-2">
                                    <div class="col-6">
                                        <button class="btn btn-info">Pesquisar</button>

                                    </div>
                                </div>

                            </div>
                        </div>




                    </div>

                </form>

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
                title: 'Confirmar a',
                content: 'pesquisa?',
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
        });
    </script>
@endpush
