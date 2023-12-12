@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                    CONTATOS DO WHATSAPP PARA SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>
            </div>


            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    {{ session(['success' =>  null ]) }}

                @elseif (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    {{ session(['error' => NULL])}}

                @endif


                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-warning" href="Cadastros">Retornar a lista de opções</a> </nav>


                    @can('WHATSAPP - ATENDIMENTO')
                        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                            <a class="btn btn-primary" href="whatsapp/atendimentoWhatsapp">Whatsapp - atendimento</a>
                        </nav>
                    @endcan



                @can('ContatosWhatsapp - INCLUIR')
                    <a href="{{ route('ContatosWhatsapp.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Incluir contatos do Whatsapp</a>
                @endcan
                <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                        <p>Total contatos do Whatsapp cadastrados no sistema de gerenciamento administrativo e contábil:
                            {{ $model->count() ?? 0 }}</p>
                    </div>
                </div>



            </div>
            @include('Api.botoesatalho')

            <tbody>
                <table class="table" style="background-color: rgb(247, 247, 213);">
                    <thead>
                        <tr>
                            <th scope="col" class="px-6 py-4">NOME</th>
                            <th scope="col" class="px-6 py-4">Ir para atender</th>
                            <th scope="col" class="px-6 py-4">Canal de entrada</th>
                            <th scope="col" class="px-6 py-4">Telefone</th>
                            <th scope="col" class="px-6 py-4"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($model as $Model)
                            <tr>

                                <td class="">
                                    {{ $Model->contactName }}
                                </td>
                                <td>
                                    @if ($Model->recipient_id)
                                        <a
                                            href="{{ route('whatsapp.atendimentoWhatsappFiltroTelefone', $Model->recipient_id) }}">{{ $Model->recipient_id }}
                                        </a>
                                @endif
                                <td>
                                    @if ($Model->entry_id)
                                        <a
                                            href="{{ route('WebhookConfig.index', $Model->entry_id) }}">{{ $Model->entry_id }}
                                        </a>
                                @endif
                               </td>
                               <td>
                                    <a
                                        href="{{ route('WebhookConfig.index', $Model->TelefoneWhatsApp->telefone) }}">{{ $Model->TelefoneWhatsApp->telefone }}
                                    </a>
                              </td>

                                @can('WHATSAPP - MENSAGEMAPROVADA')
                                    <td>
                                    <a href="{{ route('whatsapp.ConvidarMensagemAprovada', $id = $Model->recipient_id) }}" class="btn btn-primary"
                                        tabindex="-1" role="button" aria-disabled="true">Enviar mensagem padrão</a>
                                    </td>
                                @endcan

                                @can('ContatosWhatsapp - EDITAR')
                                    <td>
                                        <a href="{{ route('ContatosWhatsapp.edit', $Model->id) }}" class="btn btn-success" tabindex="-1"
                                            role="button" aria-disabled="true">Editar</a>
                                    </td>
                                @endcan

                                @can('ContatosWhatsapp - VER')
                                    <td>
                                        <a href="{{ route('ContatosWhatsapp.show', $Model->id) }}" class="btn btn-info" tabindex="-1"
                                            role="button" aria-disabled="true">Ver</a>
                                    </td>
                                @endcan

                                @can('ContatosWhatsapp - EXCLUIR')
                                    <td>
                                        {{-- <form method="POST" action="{{ route('TipoEsporte.destroy', $Model->id)->with($Model->nome) }}">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-danger">
                                                Excluir
                                            </button>
                                        </form> --}}


                                        <form method="POST" action="{{ route('ContatosWhatsapp.destroy', [$Model->id]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="nome" value="{{ $Model->nome }}">
                                            <button type="submit" class="btn btn-danger">
                                                Excluir
                                            </button>
                                        </form>

                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @include('Api.botoesatalho')

                                <div class="badge bg-primary text-wrap" style="width: 100%;">
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
