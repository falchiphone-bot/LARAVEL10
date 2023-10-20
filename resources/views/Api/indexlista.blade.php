@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">

        <div class="card">
            <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                MENSAGENS VIA API WHATSAPP PARA SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
            </div>
        </div>

        @can('WHATSAPP - MENSAGEMAPROVADA')
                        <td>
                            <a href="{{ route('whatsapp.enviarMensagemAprovada') }}" class="btn btn-danger" tabindex="-1" role="button" aria-disabled="true">Agradecimento pelo contato</a>
                        </td>
        @endcan
        @can('WHATSAPP - MENSAGEMNOVA')
                        <td>
                            <a href="{{ route('whatsapp.enviarMensagemNova') }}" class="btn btn-danger" tabindex="-1" role="button" aria-disabled="true">Esta é uma mensagem livre de teste</a>
                        </td>
        @endcan

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
                <a class="btn btn-warning" href="/dashboard">Retornar a lista de opções</a>
            </nav>



            <div class="card-header">
                <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                    <p>Total de registros no sistema de gerenciamento administrativo e contábil:
                        {{ count($model) ?? 0 }}
                    </p>
                </div>
            </div>



        </div>

        <tbody>
            <table class="table" style="background-color: rgb(247, 247, 213);">
                <thead>
                    <tr>
                        <th scope="col" class="px-6 py-4">DATA</th>
                        <th scope="col" class="px-6 py-4">TIPO</th>
                        <th scope="col" class="px-6 py-4">CONTATO</th>

                        <th scope="col" class="px-6 py-4">TELEFONE</th>
                        <th scope="col" class="px-6 py-4">MENSAGEM</th>
                        <th scope="col" class="px-6 py-4">ANEXO</th>
                        <th scope="col" class="px-6 py-4">NOME ANEXO</th>

                    </tr>
                </thead>

                <tbody>

                    @foreach ($model as $models)
                    <?php

                        $dateString = $models['created_at'];
                        $dateTime = new DateTime($dateString);
                        $formattedDate = $dateTime->format("d/m/Y H:i:s");
                    ?>

                    <tr>
                        <td class="">
                            {{
                                $formattedDate
                            }}

                        </td>
                        <td class="">
                            {{ $models["type"] }}
                        </td>
                        <td class="">
                            {{ $models['contactName'] }}
                        </td>
                        <td class="">
                            {{ $models['waId'] }}
                        </td>
                        <td class="">
                             @if($models['caption'])
                                {{ $models['caption'] ?? null }}
                            @elseif ( $models['mime_type'])
                                 {{ $models['body'] ?? null }}
                             @elseif ( $models['body'])
                                 {{ $models['body'] ?? null }}
                            @endif
                        </td>
                        <td class="">
                            @if($models['image_mime_type'])
                                {{ $models['image_mime_type'] ?? null }}
                            @elseif ( $models['mime_type'])
                             {{ $models['mime_type'] ?? null }}
                            @endif
                        </td>
                        <td class="">
                            {{ $models['filename'] ?? null }}
                        </td>

                        @can('CATEGORIAS - VER')
                        <td>
                            <a href="{{ route('whatsapp.registro', $models['id']) }}" class="btn btn-info" tabindex="-1" role="button" aria-disabled="true">Ver</a>
                        </td>
                        @endcan
                    </tr>
                    @endforeach


                </tbody>
            </table>
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
