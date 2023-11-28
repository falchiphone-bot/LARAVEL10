
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


                <td><a
                        href="{{ route('whatsapp.atendimentoWhatsappFiltroTelefone', $item->recipient_id) }}">{{ $item->contactName }}</a>
                </td>
                <td>


                    @if ($item->quantidade_nao_lida > 0)
                        {{ $item->updated_at->format("d/m/Y h:m")}}
                        <button class="bg-success text-white">
                            {{ $item->quantidade_nao_lida }}
                        </button>
                    @else
                     {{ $item->updated_at->format("d/m/Y")}}

                    @endif
                </td>


            </tr>
        @endforeach

        </table>
    </div>
