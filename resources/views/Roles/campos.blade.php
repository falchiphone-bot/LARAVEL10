   @csrf
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <label for="nome">Nome</label>
                    <input class="form-control @error('name') is-invalid @else is-valid @enderror" name="name"
                        type="text" id="name" value="{{$cadastro->name??null}}">
                    @error('name')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div class="row mt-2">
                <div class="col-6">
                    <button class="btn btn-primary">Salvar</button>
                    <a href="{{route('Funcoes.index')}}" class="btn btn-secondary">Retornar para lista</a>
                </div>
            </div>
        </div>
    </div>



