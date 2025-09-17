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

        <div class="form-group">
            <label for="EmpresaID">Empresa</label>
            <select required class="form-control select2 @error('EmpresaID') is-invalid @else is-valid @enderror" id="EmpresaID" name="EmpresaID">
                <option value="">Selecione a empresa</option>
                @foreach(($empresas ?? []) as $empresa)
                    <option value="{{ $empresa->ID }}" @selected(old('EmpresaID', session('EmpresaID')) == $empresa->ID)>
                        {{ $empresa->Descricao }}
                    </option>
                @endforeach
            </select>
            @error('EmpresaID')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
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

        <div class="form-group">
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

        <div class="form-group">
            <label for="nome">Nome</label>
            <input required class="form-control @error('nome') is-invalid @else is-valid @enderror" name="nome"
                type="text" id="nome" value="{{ $model->nome ?? null }}">
            @error('nome')
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
            <input required class="form-control @error('telefone') is-invalid @else is-valid @enderror"
                name="telefone" type="text" id="telefone" value="{{ $model->telefone ?? null }}">
            @error('telefone')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mt-3">
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

        <div class="row mt-12">
            <div class="col-12 text-center">
                <button class="btn btn-primary">Salvar ficha do representante</button>
            </div>
        </div>
        </form>
        <hr>

        <div class="row mt-12">
            <div class="col-12 d-flex justify-content-center">
                <a href="{{ route('Representantes.index') }}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
            $('#cpf').inputmask('999.999.999-99', {
                clearMaskOnLostFocus: false
            });
            $('#cnpj').inputmask('99.999.999/9999-99', {
                clearMaskOnLostFocus: false
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
        });
    </script>
@endpush
