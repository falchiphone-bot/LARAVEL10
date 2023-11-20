
<div class="row">
    <div class="col-3">
        <table>
            {{-- <thead>
                {{-- <tr>
                                        <th>Nome</th> --}}
                {{-- <th>Telefone</th> --}}


            {{-- </thead> --}}

                @foreach ($Contatos as $item)
                    <tr>
                        <td>
                            <a
                                href="{{ route('whatsapp.atendimentoWhatsappFiltroTelefone', $item->Contato->recipient_id) }}">{{ $item->Contato->contactName }}
                            </a>
                        </td>

                        <td>
                            @if ($item->Contato->quantidade_nao_lida > 0)
                                {{ $item->Contato->updated_at->format('d/m/Y H:i') }}
                                <button class="bg-success text-white">
                                    {{ $item->Contato->quantidade_nao_lida }}
                                </button>
                            @else
                                {{ $item->Contato->updated_at->format('d/m/Y') }}
                            @endif
                        </td>
                    </tr>
                @endforeach

        </table>
    </div>
