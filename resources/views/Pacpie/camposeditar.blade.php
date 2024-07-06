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
                <label for="cnpj">CNPJ</label>
                <input class="form-control @error('cnpj') is-invalid @else is-valid @enderror" name="cnpj"
                    type="text" id="cnpj" value="{{ $model->cnpj ?? null }}">
                @error('cnpj')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                @can('PACPIE - LIBERA VALIDAR CNPJ')
                    <input type="checkbox" name="liberacnpj" value="1">
                    <label for="checkbox_liberacnpj">Libera validação do CNPJ</label>
                    <br>
                @endcan
                @can('PACPIE - LIMPA CAMPO CNPJ')
                    <input type="checkbox" name="limpacnpj" value="1">
                    <label for="checkbox_limpacnpj">Limpa campo CNPJ</label>
                    <br>
                @endcan
            </div>

            <div class="form-group">
                <div class="badge bg-warning text-wrap" style="width: 100%; height: 50%; font-size: 24px;">
                    ORIGEM DO CADASTRO
                </div>


                <div class="form-group">
                    <label for="nome">Originado de: </label>
                    <select required class="form-control select2" id="origem_cadastro" name="origem_cadastro">
                        <option value="">Selecionar origem das empresas</option>
                        @foreach ($OrigemPacpie as $origempacpie)
                            <option @if ($retorno['origem_cadastro'] == $origempacpie->id) selected @endif value="{{ $origempacpie->id }}">
                                {{ $origempacpie->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>



                <div class="badge bg-danger text-wrap" style="width: 100%; height: 25%; font-size: 18px;">
                    o acima é a origem do cadastro da empresa
                </div>



            </div>


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
                <input   class="form-control @error('email') is-invalid @else is-valid @enderror" name="email"
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

            <div class="col-6">
                <label for="emailprimeirocontato">Enviado email para primeiro contato</label>
                <input type="hidden" name="emailprimeirocontato" value="0"> <!-- Para garantir que "0" seja enviado quando o checkbox estiver desmarcado -->
                <input
                    name="emailprimeirocontato"
                    type="checkbox"
                    id="emailprimeirocontato"
                    value="1"
                    {{ old('emailprimeirocontato', $model->emailprimeirocontato ?? 0) == 1 ? 'checked' : '' }}>
                @error('emailprimeirocontato')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-6">
                <label for="emailcomfalha">Email com falha</label>
                <input type="hidden" name="emailcomfalha" value="0"> <!-- Para garantir que "0" seja enviado quando o checkbox estiver desmarcado -->
                <input
                    name="emailcomfalha"
                    type="checkbox"
                    id="emailcomfalha"
                    value="1"
                    {{ old('emailcomfalha', $model->emailcomfalha ?? 0) == 1 ? 'checked' : '' }}>
                @error('emailcomfalha')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>



        </div>

        <div class="row mt-12">
            <div class="col-12 text-center">
                <button class="btn btn-primary">Salvar ficha do representante</button>
            </div>
        </div>
        </form>
        <hr>

        <div class="row mt-12">
            <div class="col-12 d-flex justify-content-center">
                <a href="{{ route('Pacpie.index') }}" class="btn btn-warning">Retornar para lista</a>
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
