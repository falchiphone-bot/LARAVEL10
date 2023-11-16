<div class="row">
                        <div class="col-3">
                            <table>
                                <thead>
                                    {{-- <tr>
                                        <th>Nome</th> --}}
                                    {{-- <th>Telefone</th> --}}

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($Contatos as $item)
                                        <tr>


                                            <td><a
                                                    href="{{ route('whatsapp.atendimentoWhatsappFiltroTelefone', $item->Contato->recipient_id) }}">{{ $item->Contato->contactName }}</a>
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
                                </tbody>
                            </table>
                        </div>

                        <div class="col-9">
                            <div class="card">
                                @can('WHATSAPP - LISTAR')
                                    <div class="card-footer">

                                        <a href="{{ route('whatsapp.indexlista') }}">Retornar para a lista</a>

                                    </div>
                                @endcan




                                {{-- <div class="card-body" style="max-width: 1024px; max-height: 3096px;"> --}}
                                    @can('WHATSAPP - VISUALIZAR MENSAGENS SEM ATENDER')
                                         @include('Api.atendimento.cabecalhotabelamensagens')
                                         @include('Api.atendimento.tabelamensagens')
                                    @endcan

                                </div>


                            </div>


                        </div>
                    </div>
</div>
