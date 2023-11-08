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


                    <form action="{{ route('whatsapp.enviarMensagemResposta', $id) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="card-body">
                        <h1>Preencher Texto da Mensagem</h1>

                             <div class="form-group">
                                <h3><label for="para">Para:</label></h3>
                                {{ $model->contactName}}
                            </div>

                            <div class="form-group">
                                <h4><label for="telefone">Telefone:</label><h4>
                                {{ $model->messagesFrom}}
                            </div>

                            <div class="form-group">
                               <h5> <label for="body">Última mensagem recebida:</label><h5>
                                Texto:{{ $model->body}}
                            </div>

                            <div class="col-sm-6">
                            <label for="token_type" style="color: black;">Tipo de Token</label>
                            <select required class="form-control" id="token_type" name="token_type">
                                <option value="token24horas">Token 24 Horas</option>
                                <option value="tokenpermanenteusuario">Token Permanente do Usuário</option>
                            </select>
                        </div>



                            <div class="form-group">
                                <label for="mensagem">Mensagem:</label>
                                <textarea id="mensagem" name="mensagem" rows="4" cols="50" class="form-control"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="arquivo">Selecionar um arquivo:</label>
                                <input type="file" id="arquivo" name="arquivo" class="form-control-file">
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
