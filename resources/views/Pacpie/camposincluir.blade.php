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

        <div class="row mt-12">
            <div class="col-12 text-center">
                <button class="btn btn-primary">Salvar ficha da empresa</button>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>

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
