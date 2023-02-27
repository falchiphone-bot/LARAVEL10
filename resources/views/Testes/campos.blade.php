   @csrf
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <label for="nome">Nome</label>
                    <input class="form-control @error('nome') is-invalid @else is-valid @enderror" name="nome"
                        type="text" id="nome" value="{{$cadastro->nome??null}}">
                    @error('nome')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

            </div>
            <div class="row">
                <div class="col-6">
                    <label for="email">Email</label>
                    <input class="form-control @error('email') is-invalid @else is-valid @enderror" name="email"
                        type="email" id="email" value="{{$cadastro->email??null}}">
                        @error('email')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-6">
                    <button class="btn btn-primary">Salvar</button>
                    <a href="{{route('Testes.index')}}" class="btn btn-secondary">Retornar para lista</a>
                </div>
            </div>
        </div>
    </div>



