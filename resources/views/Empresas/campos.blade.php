   @csrf
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <label for="nome">DESCRIÇÃO</label>
                    <input class="form-control @error('Descricao') is-invalid @else is-valid @enderror" name="Descricao"
                        type="text" id="Descricao" value="{{$cadastro->Descricao??null}}">
                    @error('nome')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

            </div>
            <div class="row">
                <div class="col-6">
                    <label for="email">CNPJ</label>
                    <input class="form-control @error('Cnpj') is-invalid @else is-valid @enderror" name="Cnpj"
                        type="text" id="Cnpj" value="{{$cadastro->Cnpj??null}}">
                        @error('Cnpj')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-6">
                    <button class="btn btn-primary">Salvar</button>
                    <a href="{{route('Empresas.index')}}" class="btn btn-secondary">Retornar para lista</a>
                </div>
            </div>
        </div>
    </div>



