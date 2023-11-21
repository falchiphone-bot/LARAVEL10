@can('WHATSAPP - ATENDIMENTO - TRANSFERIR ATENDIMENTO')

    <div class="card-body" style="background-color: #add8e6;">

        <form method="GET" action="{{ route('whatsapp.TransferirAtendimento', $NomeAtendido->id) }}" accept-charset="UTF-8">
            @csrf

            <div class="row">
                <div class="col-sm-6">
                    <label for="UsuarioID" style="color: black;">Usuários/atendentes</label>
                    <select required class="form-control select2" id="UsuarioID" name="UsuarioID">
                        <option value="">Selecionar usuário</option>
                        @if ($Usuarios)
                            @foreach ($Usuarios as $item)
                                <option value="{{ $item->email }}">
                                    {{ $item->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-sm-6">
                    <button class="btn btn-primary">Transferir atendimento</button>
                    {{-- <p>Transferido para: {{ $NomeAtendido->transferido_para }}</p> --}}
                </div>

            </div>


        </form>


    </div>

@endcan
