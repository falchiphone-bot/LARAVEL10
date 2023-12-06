
<div class="row">
    <div class="col-3">
        <table>
            {{-- <thead>
                {{-- <tr>
                                        <th>Nome</th> --}}
                {{-- <th>Telefone</th> --}}


            {{-- </thead> --}}

            @foreach ($RegistrosContatos  as $item)
            <tr>
                @if ($item->recipient_id)
                    <td>
                    <a
                        href="{{ route('whatsapp.atendimentoWhatsappFiltroTelefone', $item->recipient_id) }}">{{ $item->contactName }}
                    </a>
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
