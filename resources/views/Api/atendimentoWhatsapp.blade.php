@extends('layouts.bootstrap5')

@section('content')
<div class="py-5 bg-light">
    <div class="container">
        <div class="card">
            <div class="badge bg-primary text-wrap" style="width: 100%;">
                ATENDIMENTO - WHATSAPP - SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
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
               <div class="col-4">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Telefone</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($Contatos as $item)
                                <tr>
                                    <td><a href="{{ $item->Contato->recipient_id }}">{{ $item->Contato->contactName }}</a></td>
                                    <td>{{ $item->Contato->recipient_id }}</td>

                                    <td>
                                        {{-- <a href="{{ route('whatsapp.enviar', $item->id) }}" class="btn btn-primary">Enviar Mensagem</a> --}}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
               </div>
               <div class="col-8">
                <div class="card">
                    <div class="card-footer">
                        <a href="{{ route('whatsapp.indexlista') }}">Retornar para a lista</a>
                    </div>




                    <div class="card-body">


                             <div class="col-12">
                                
                             </div>



                            <div class="form-group">
                                <label for="mensagem">Mensagem:</label>
                                <textarea id="mensagem" name="mensagem" rows="4" cols="50" class="form-control"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Enviar Mensagem</button>

                    </div>
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
