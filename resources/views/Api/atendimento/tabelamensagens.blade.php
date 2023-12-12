
   <!DOCTYPE html>
   <html lang="en">
   <head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title>CANAL DE ATENDIMENTO WHATSAPP - SISTEMA </title>
       <style>
           .red-strong {
               color: red;
               font-weight: bold;
           }
       </style>
   </head>




<div class="card-body" style="background-color: #b3ffb3;">
      <div class="col-12">
        <table class="table-responsive">
            <thead>
                <tr>
                    @if($QuantidadeCanalAtendimento == 1)
                        <th></th>
                    @else
                        <th scope="col" class="px-2 py-2 bg-dark text-light">Entrada</th>
                    @endif

                    <th scope="col" class="px-2 py-2 bg-dark text-light">Recebida</th>
                    <th scope="col" class="px-2 py-2 bg-dark text-light">Enviada</th>
                    <th scope="col" class="px-2 py-2 bg-dark text-light">Data</th>
                </tr>

            </thead>
            @if ($selecao)
                <tbody>
                    @foreach ($selecao as $item)

                        @if ($item->status === 'delivered' || $item->status === 'read')
                            @continue
                        @endif

                        @if ($item->status == 'sent' && $item->conversation_id !== null)
                            @continue
                        @endif

                        <tr>

                            <td>
                                <hr>
                               @include('Api.atendimento.statusatendimentomensagens')
                               @if($QuantidadeCanalAtendimento > 1)

                               <span class="red-strong">{{ $item->Entrada->usuario }}</span><br>
                                    {{ '('. $item->Entrada->telefone . ')'}}
                                    <hr>
                                @endif
                            </td>

                            <td>
                                @include('Api.atendimento.mostracorpomensagemtabela')
                                @can('WHATSAPP - ATUALIZAR REGISTRO - BAIXAR URL MIDIA')
                                    @include('Api.baixarmidiacriaurl')
                                    @include('Api.mostraimagem')
                                    @include('Api.mostradocumento')
                                    @include('Api.mostravideo')
                                    @include('Api.mostraaudio')

                                @else
                                    @if ($NomeAtendido->user_atendimento !== null)
                                        @include('Api.baixarmidiacriaurl')
                                        @include('Api.mostraimagem')
                                        @include('Api.mostradocumento')
                                        @include('Api.mostravideo')
                                        @include('Api.mostraaudio')
                                    @endif

                                @endcan
                            </td>

                            <td>
                                @include('Api.atendimento.mostracorpomensagemtabelaenviada')

                            </td>
                            <?php
                            $dateString = $item['created_at'];
                            $dateTime = new DateTime($dateString);
                            $formattedDate = $dateTime->format('d/m/Y H:i:s');
                            ?>
                            @if (
                                $NomeAtendido->status_mensagem_enviada == 0 &&
                                    $item->status == 'sent' &&
                                    $item['created_at'] > $NomeAtendido->ultima_leitura)
                                <td style="background-color: red;">
                                    {{ $formattedDate ?? null }}
                                </td>
                            @else
                                <td>
                                    {{ $formattedDate ?? null }}
                                </td>
                            @endif


                        </tr>
                    @endforeach

                </tbody>
            @endif
        </table>
    </div>
</div>
