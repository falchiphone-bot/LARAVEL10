   @csrf
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <label for="text">Função</label>
                    <input class="form-control @error('role_id') is-invalid @else is-valid @enderror" name="role_id"
                           type="number" id="role_id" value="{{$cadastro->role_id??null}}">
                    @error('role_id')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <label for="nome">Tipo tabela</label>
                    <input class="form-control @error('model_type') is-invalid @else is-valid @enderror" name="model_type"
                        type="text" id="model_type" value="{{$cadastro->model_type??null}}">
                    @error('model_type')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <label for="nome">Tabela</label>
                    <input class="form-control @error('model_id') is-invalid @else is-valid @enderror" name="model_id"
                           type="text" id="model_id" value="{{$cadastro->model_id??null}}">
                    @error('model_id')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>


            <div class="row mt-2">
                <div class="col-6">
                    <button class="btn btn-primary">Salvar</button>
                    <a href="{{route('ModelodeFuncoes.index')}}" class="btn btn-secondary">Retornar para lista</a>
                </div>
            </div>
        </div>
    </div>



