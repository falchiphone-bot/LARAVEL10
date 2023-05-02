@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;
                ;font-size: 24px; lign=˜Center˜">
                    CLIENTE GOOGLE PARA O SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>


                <div class="card-body">

                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                        <a class="btn btn-warning" href="dashboard">Retornar a lista de opções</a>

                    </nav>
                </div>

                    <div class="badge bg-warning text-wrap" style="width: 100%; font-size: 24px; color: black; text-align: center;">
                        <div class="card">
                            <nav class="navbar navbar-success" style="background-color: hsla(234, 92%, 47%, 0.096);">
                                  DADOS DO CLIENTE GOOGLE
                            </nav>
                      </div>


                      <div>

                        <ul>
                            {{-- <li><strong>Arquivo de configuração:</strong> {{ $gClientInfo['authConfig'] }}</li>
                            <li><strong>Permissões:</strong> {{ implode(', ', $gClientInfo['scopes']) }}</li>
                            <li><strong>Token de acesso:</strong> {{ $gClientInfo['accessToken'] }}</li> --}}
                            <!-- Adicione outras informações que desejar aqui -->
                        </ul>
                    </div>


                </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-12">
                                    <img src="{{ asset(session('googleUser')->avatar) }}" alt="Minha imagem">
                                </div>
                                <div class="col-sm-12">
                                    <label for="name">Identificação no Google:</label>
                                    {{session('googleUser')->id}}
                                </div>
                                <div class="col-sm-12">
                                    <label for="name">Nick no Google:</label>
                                    {{session('googleUser')->nickname}}
                                </div>
                                <div class="col-sm-12">
                                    <label for="name">Família:</label>
                                    {{session('googleUser')->user['family_name']}}
                                </div>
                                <div class="col-sm-12">
                                    <label for="name">Nome:</label>
                                    {{session('googleUser')->name}}

                                </div>
                                <div class="col-sm-12">
                                    <label for="inicio">Email:</label>
                                    {{session('googleUser')->email}}
                                </div>
                                <div class="col-sm-12">
                                    <label for="name">Email verificado:</label>
                                    {{session('googleUser')->user['email_verified']}}
                                </div>
                                <div class="col-sm-12">
                                    <label for="name">Localidade:</label>
                                    {{session('googleUser')->user['locale']}}
                                </div>
                                <div class="col-sm-12">
                                    <label for="name">Corporação:</label>
                                    {{session('googleUser')->user['hd']}}
                                </div>
                                <div class="col-sm-12">
                                    <label for="name">Perfil Google:</label>
                                    {{session('googleUser')->user['profile']}}
                                </div>
                                <div class="col-sm-12">
                                    <label for="name">link-----plus :</label>
                                    {{session('googleUser')->user['link']}}
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            {{-- <button class='btn btn-primary'>Salvar o evento</button> --}}
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
@endpush
