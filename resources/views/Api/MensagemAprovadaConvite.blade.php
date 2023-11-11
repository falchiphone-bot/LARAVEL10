@extends('layouts.bootstrap5')

@section('content')
<div class="py-5 bg-light">
    <div class="container">
        <div class="card">
            <div class="badge bg-primary text-wrap" style="width: 100%;">
                SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
            </div>

            <div class="card-body">
                @if (session('MensagemNaoPreenchida'))
                <div class="alert alert-danger">
                    {{ session('MensagemNaoPreenchida') }}
                </div>
                @elseif (session('error'))
                <div class="alert alert-danger">
                    {{ session('errordesta') }}
                </div>
                @endif


                <div class="row">
                    <div class="card">
                        <div class="card-footer">
                            <a href="{{ route('whatsapp.indexlista') }}">Retornar para a lista</a>
                        </div>

                        <div class="card-body">
                            <h1>Selecionar a mensagem aprovada e para quem enviar</h1>


                            <form action="{{ route('whatsapp.enviarMensagemAprovada') }}" method="POST">
                                @csrf
                            <div class="col-sm-6">
                                <label for="idcontato" style="color: black;">Contatos disponíveis</label>
                                <select required class="form-control select2" id="idcontato" name="idcontato">
                                    <option value="">
                                        Selecionar contato
                                    </option>
                                    @foreach ($contatos as $contato)
                                    <option
                                        value="{{ $contato->id }}">
                                        {{ $contato->contactName }}
                                    </option>
                                    @endforeach


                                </select>
                            </div>

                            <div class="col-sm-6">
                                <label for="idtemplate" style="color: black;">Templates disponíveis</label>
                                <select required class="form-control select2" id="idtemplate" name="idtemplate">
                                    <option value="">
                                        Selecionar template
                                    </option>
                                    @foreach ($template as $template)
                                    <option
                                        value="{{ $template->id }}">
                                        {{ $template->name . ' ===>>> Texto: '. $template->texto}}
                                    </option>
                                    @endforeach


                                </select>
                            </div>


                            <div class="col-sm-6">
                            <label for="token_type" style="color: black;">Tipo de Token</label>
                            <select required class="form-control" id="token_type" name="token_type">
                                <option value="token24horas">Token 24 Horas</option>
                                <option value="tokenpermanenteusuario">Token Permanente do Usuário</option>
                            </select>
                        </div>




                                <button type="submit" class="btn btn-primary">Enviar Mensagem</button>
                            </form>


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
                content: 'Confirma o envio?',
                buttons: {
                    confirmar: function() {
                        $.confirm({
                            title: 'Confirmar!',
                            content: 'Deseja realmente continuar com o envio?',
                            buttons: {
                                confirmar: function() {
                                    e.currentTarget.submit();
                                },
                                cancelar: function() {
                                    // Você pode adicionar ações aqui, se necessário.
                                },
                            }
                        });
                    },
                    cancelar: function() {
                        // Você pode adicionar ações aqui, se necessário.
                    },
                }
            });
        });
    </script>
    @endpush
