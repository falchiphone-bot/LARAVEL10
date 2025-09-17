@csrf
<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            {{ session(['success' => null]) }}
        @elseif (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            {{ session(['error' => null]) }}
        @endif

        <div class="row">
            <div class="col-6">
                <label for="cpf">CPF</label>
                <input required class="form-control @error('cpf') is-invalid @else is-valid @enderror" name="cpf"
                    type="text" id="cpf" value="{{ $model->cpf ?? null }}">
                @error('cpf')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                @can('REPRESENTANTES - LIBERA VALIDAR CPF')
                    <input type="checkbox" name="liberacpf" value="1">
                    <label for="checkbox_liberacpf">Libera validação do CPF</label>
                    <br>
                @endcan
                @can('REPRESENTANTES - LIMPA CAMPO CPF')
                    <input type="checkbox" name="limpacpf" value="1">
                    <label for="checkbox_limpacpf">Limpa campo do CPF</label>
                    <br>
                @endcan
            </div>

            <div class="col-6">
                <label for="cnpj">CNPJ</label>
                <input class="form-control @error('cnpj') is-invalid @else is-valid @enderror" name="cnpj"
                    type="text" id="cnpj" value="{{ $model->cnpj ?? null }}">
                @error('cnpj')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                @can('REPRESENTANTES - LIBERA VALIDAR CNPJ')
                    <input type="checkbox" name="liberacnpj" value="1">
                    <label for="checkbox_liberacnpj">Libera validação do CNPJ</label>
                    <br>
                @endcan
                @can('REPRESENTANTES - LIMPA CAMPO CNPJ')
                    <input type="checkbox" name="limpacnpj" value="1">
                    <label for="checkbox_limpacnpj">Limpa campo CNPJ</label>
                    <br>
                @endcan
            </div>

            <div class="col-6">
                <label for="nome">Nome</label>
                <input required class="form-control @error('nome') is-invalid @else is-valid @enderror" name="nome"
                    type="text" id="nome" value="{{ $model->nome ?? null }}">
                @error('nome')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-6">
                <label for="email">Email</label>
                <input required class="form-control @error('email') is-invalid @else is-valid @enderror" name="email"
                    type="text" id="email" value="{{ $model->email ?? null }}">
                @error('email')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-6">
                <label for="telefone">Telefone</label>
                <input required class="form-control @error('telefone') is-invalid @else is-valid @enderror"
                    name="telefone" type="text" id="telefone" value="{{ $model->telefone ?? null }}">
                @error('telefone')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12 mt-3">
                <label style="display:block;">Registro do representante</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="agente_fifa" name="agente_fifa" value="1" @checked(old('agente_fifa', $model->agente_fifa ?? false))>
                    <label class="form-check-label" for="agente_fifa">Agente FIFA</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="oficial_cbf" name="oficial_cbf" value="1" @checked(old('oficial_cbf', $model->oficial_cbf ?? false))>
                    <label class="form-check-label" for="oficial_cbf">Oficial CBF</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="sem_registro" name="sem_registro" value="1" @checked(old('sem_registro', $model->sem_registro ?? false))>
                    <label class="form-check-label" for="sem_registro">Sem registro</label>
                </div>
            </div>
            <div class="col-6">
                <label for="telefone">Tipo de representante</label>
                <select class="form-control select2" id="tipo_representante" name="tipo_representante">
                    <option value="">
                        Tipos de representantes
                    </option>
                    @foreach ($tipor as $item)
                        <option @required(true) @if ($tiporep['tiporepresentante'] == $item->id) selected @endif
                            value="{{ $item->id }}">
                            {{ $item->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mt-12">
            <div class="col-12 text-center">
                <button class="btn btn-primary">Salvar ficha do representante</button>
            </div>
        </div>
        </form>
        <hr>
        <div class="card-body">
            {{-- //////////////////////////////////// REDES SOCIAIS --}}
            <form method="POST" action="/Representantes/CreateRedeSocialRepresentantes" accept-charset="UTF-8">
                @csrf
                <input required
                    class="form-control @error('RedeSocialRepresentante_id') is-invalid @else is-valid @enderror d-none"
                    name="RedeSocialRepresentante_id" type="text" id="RedeSocialRepresentante_id"
                    value="{{ $model->id ?? null }}">
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
                    <input required
                        class="form-control @error('RedeSocial_complemento') is-invalid @else is-valid @enderror"
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
                                        Excluir
                                    </button>
                                </form>
                            </td>
                        @endcan
                    </tr>
                @endforeach
            </table>
            {{-- //////////////////////////////////// FIM REDES SOCIAIS --}}
        </div>
        <div class="row mt-12">
            <div class="col-12 d-flex justify-content-center">
                <a href="{{ route('Representantes.index') }}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });

        function syncFlags() {
            const sem = $('#sem_registro').is(':checked');
            $('#agente_fifa, #oficial_cbf').prop('disabled', sem);
            if (sem) {
                $('#agente_fifa, #oficial_cbf').prop('checked', false);
            }
            const anyReg = $('#agente_fifa').is(':checked') || $('#oficial_cbf').is(':checked');
            if (anyReg) {
                $('#sem_registro').prop('checked', false);
            }
        }
        $('#sem_registro, #agente_fifa, #oficial_cbf').on('change', syncFlags);
        syncFlags();

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
