
<div class="row">
    <div class="col-3">
        <table>
            {{-- <thead>
                {{-- <tr>
                                        <th>Nome</th> --}}
                {{-- <th>Telefone</th> --}}


            {{-- </thead> --}}
            @can('WHATSAPP - ATENDIMENTO - REABRIR ATENDIMENTO')
            <nav class="navbar navbar-red" style="background-color: hsla(158, 92%, 47%, 0.635);">
            <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                <a href="{{ route('ContatosWhatsapp.index') }}" class="btn btn-success" tabindex="-1"
                role="button" aria-disabled="true">Mais contatos...</a>
            </div>
           </nav>

            @endcan



            @foreach ($RegistrosContatos  as $item)
            <tr>
                @if ($item->recipient_id)
                    <td>
                    @if($QuantidadeCanalAtendimento > 1)
                    <a href="{{ route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $item->recipient_id, 'entry_id' => $item->entry_id]) }}">
                        {{ $item->contactName }} <span style="color: red;"> <br>Canal -> <span style="color: black;">{{ $item->TelefoneWhatsApp->usuario }}</span>
                    </a>

                    @else
                       <a href="{{ route('whatsapp.atendimentoWhatsappFiltroTelefone', ['recipient_id' => $item->recipient_id, 'entry_id' => $item->entry_id]) }}">
                            {{ $item->contactName }}
                        </a>
                 @endif

                    <hr>
                    </td>
                @endif
            <td>

                @if ($item->quantidade_nao_lida > 0)
                    {{ $item->updated_at->format("d/m/Y h:m")}}
                    <button class="bg-success text-white">
                        {{ $item->quantidade_nao_lida }}
                    </button>
                @else
                @if ($item->recipient_id)
                    {{ $item->updated_at->format("d/m/Y")}}
                @endif
                @endif
            </td>
        </tr>
        @endforeach

        </table>
    </div>
