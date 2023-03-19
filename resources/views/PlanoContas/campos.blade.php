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
                    <label for="email">Grau</label>
                    <input class="form-control @error('Cnpj') is-invalid @else is-valid @enderror" name="Grau"
                        type="text" id="Grau" value="{{$cadastro->Grau??null}}">
                        @error('Grau')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-6">
                    <button class="btn btn-primary">Salvar</button>
                    <a href="{{route('PlanoContas.index')}}" class="btn btn-secondary">Retornar para lista</a>
                </div>
            </div>
        </div>
    </div>



