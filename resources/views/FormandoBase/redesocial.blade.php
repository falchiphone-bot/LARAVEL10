
    <div class="card-body" style="background-color: #C8E6C9;">
        @can('REDESOCIAL - LISTAR')

            <nav class="navbar navbar-info" style="background-color: hsla(234, 92%, 47%, 0.096);">
                <a class="btn btn-info" href="/RedeSocial" style="display: inline-block;" target="_blank">Cadastro de redes sociais</a>
            </nav>
    @endcan
    {{-- ////////////////////////////////////  REDES SOCIAIS --}}
    <form method="POST" action="/FormandoBase/CreateRedeSocialFormandoBase" accept-charset="UTF-8">
        @csrf

        <input required
            class="form-control @error('RedeSocialFormandoBase_id') is-invalid @else is-valid @enderror d-none"
            name="RedeSocialFormandoBase_id" type="text" id="RedeSocialFormandoBase_id" value="{{ $model->id ?? null }}">


        <div class="col-6">
            <label for="Limite" style="color: black;">Incluir redes sociais</label>
            <select class="form-control select2" id="RedeSocial" name="RedeSocial">
                <option value="">
                    Selecionar rede social
                </option>
                @foreach ($RedeSocial as $redesociais)
                    <option @required(true) @if ($retorno['redesocial'] == $redesociais->id) selected @endif
                        value="{{ $redesociais->id }}">
                        {{ $redesociais->nome }}

                    </option>
                @endforeach
            </select>
            <label for="Limite" style="color: black;">Incluir link para a rede social</label>
            <input required class="form-control @error('RedeSocial_complemento') is-invalid @else is-valid @enderror"
                name="RedeSocial_complemento" type="text" id="RedeSocial_complemento"
                value="{{ $model->RedeSocial_complemento ?? null }}">
        </div>

        <div class="row mt-2">
            <div class="col-2">
                <button class="btn btn-danger">Salvar rede social</button>

            </div>
        </div>
    </form>
    <table>
        @if ($redeSocialExiste)

            <tr>
                <th>Rede Social</th>
                <th>Link</th>
                <th></th>
            </tr>



            @foreach ($redesocialUsuario as $item)
                <style>
                    table {
                        border-collapse: collapse;
                        width: 100%;
                    }

                    th,
                    td {
                        border: 1px solid black;
                        padding: 8px;
                    }

                    th {
                        background-color: #f2f2f2;
                    }
                </style>


                <tr>
                    <td>{{ $item->RedeSociais->nome ?? null }}:</td>
                    <td><a href="{{ $item->RedeSocial_complemento ?? null }}"
                            target="_blank">{{ $item->RedeSocial_complemento ?? null }}</a></td>

                    @can('REDESOCIALUSUARIO - EXCLUIR')
                        <td>
                            <form method="POST" action="{{ route('RedeSocialUsuarios.destroy', $item->id) }}">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-danger">
                                    Excluir rede social
                                </button>
                            </form>
                        </td>
                    @endcan
                </tr>
            @endforeach

        @endif
    </table>


    {{-- //////////////////////////////////// FIM REDES SOCIAIS --}}
</div>
