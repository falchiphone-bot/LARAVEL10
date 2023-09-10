@csrf
<div class="card">
    <div class="card-body" style="background-color: #cecccc;">
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
        <div class="row mt-12">
            <div class="col-12 d-flex justify-content-center">
                <a href="{{ route('FormandoBase.index') }}" class="btn btn-warning">Retornar para lista de formandos/atletas</a>
            </div>
        </div>

        @can('FORMANDOBASERECEBIMENTOS - LISTAR')
            <th>
                <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-success" href="/FormandoBaseRecebimentos">Todos recebimentos e representantes</a>
                    {{-- <a href="{{ route('FormandoBaseRecebimentos.edit', $model->formandobase_id) }}" class="btn btn-success" tabindex="-1" role="button" aria-disabled="true">Editar</a> --}}
                </nav>
            </th>
        @endcan

        <div class="form-group">
            <div class="badge bg-primary text-wrap" style="width: 100%; height: 50%; font-size: 24px;">
                CLUBE
            </div>
            <select required class="form-control select2" id="EmpresaSelecionada" name="EmpresaSelecionada">
                <option value="">Selecionar clube</option>
                @foreach ($Empresas as $Empresa)
                    <option @if ($retorno['EmpresaSelecionada'] == $Empresa->ID) selected @endif value="{{ $Empresa->ID }}">
                        {{ $Empresa->Descricao }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="cpf">CPF</label>
            <input required class="form-control @error('cpf') is-invalid @else is-valid @enderror" name="cpf"
                type="text" id="cpf" value="{{ $model->cpf ?? null }}">
            @error('cpf')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

            @can('FORMANDOBASE - LIBERA VALIDAR CPF')
                <input type="checkbox" name="liberacpf" value="1">
                <label for="checkbox_liberacpf">Libera validação do CPF</label>
                <br>
            @endcan
            @can('FORMANDOBASE - LIMPA CAMPO CPF')
                <input type="checkbox" name="limpacpf" value="1">
                <label for="checkbox_limpacpf">Limpa campo do CPF</label>
                <br>
            @endcan
        </div>
        <div class="form-group">
            <label for="nome">Nome</label>
            <input required class="form-control @error('nome') is-invalid @else is-valid @enderror" name="nome"
                type="text" id="nome" value="{{ $model->nome ?? null }}">
            @error('nome')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="nascimento">Nascimento</label>
            <input required class="form-control @error('nascimento') is-invalid @else is-valid @enderror"
                name="nascimento" type="date" id="nascimento"
                value="{{ $model->nascimento->format('Y-m-d') ?? null }}">
            @error('nascimento')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input required class="form-control @error('email') is-invalid @else is-valid @enderror" name="email"
                type="text" id="email" value="{{ $model->email ?? null }}">
            @error('email')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="telefone">Telefone</label>
            <input required class="form-control @error('telefone') is-invalid @else is-valid @enderror" name="telefone"
                type="text" id="telefone" value="{{ $model->telefone ?? null }}">
            @error('telefone')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>



        <input required class="form-control @error('formandobase_id') is-invalid @else is-valid @enderror d-none"
            name="formandobase_id" type="text" id="formandobase_id" value="{{ $model->id ?? null }}">


        <div class="col-6">
            <label for="Limite" style="color: black;">Representante principal</label>
            <select required class="form-control select2" id="representante_id" name="representante_id">
                <option value="">
                    Selecionar representante principal
                </option>
                @foreach ($representantes as $representante)
                    <option @if ($representante ?? null) @if ($model->representante_id == $representante->id) selected @endif
                        @endif
                        value="{{ $representante->id }}">
                        {{ $representante->nome }}
                    </option>
                @endforeach
            </select>
        </div>



        <hr>


        <div class="row mt-12">
            <div class="col-12 text-center">
                <button class="btn btn-primary">Salvar ficha do formando</button>
            </div>
        </div>
 </form>
        <hr>

        {{-- ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
        @include('FormandoBase.avaliacao')
        {{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}



        {{-- ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
        @include('FormandoBase.posicoes')
        {{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}

        {{-- ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
        @include('FormandoBase.recebimentos')
        {{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
        {{-- ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
        @include('FormandoBase.recebimentosFormandoBase')
        {{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}


        {{-- ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
        @include('FormandoBase.redesocial')
        {{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}


        {{-- ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
        @include('FormandoBase.arquivos')
        {{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}



        <div class="row mt-12">
            <div class="col-12 d-flex justify-content-center">
                        <a href="{{ route('FormandoBase.index') }}" class="btn btn-warning">Retornar para lista de formandos/atletas</a>
        </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
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

    <script>
        $(document).ready(function() {
            $('#cpf').inputmask('999.999.999-99', {
                clearMaskOnLostFocus: false
            });
            $('#cnpj').inputmask('99.999.999/9999-99', {
                clearMaskOnLostFocus: false
            });
        });
    </script>
@endpush
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.js"></script>
    <script>

        $(document).ready(function() {
            $('.money').mask('000.000.000.000.000,00', {reverse: true});
        });
    </script>
@endpush
