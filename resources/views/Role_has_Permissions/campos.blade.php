   @csrf
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <label for="text">Permissão</label>
                    <input class="form-control @error('permission_id') is-invalid @else is-valid @enderror" name="permission_id"
                           type="number" id="permission_id" value="{{$cadastro->permission_id??null}}">
                    @error('permission_id')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <label for="nome">Função</label>
                    <input class="form-control @error('role_id') is-invalid @else is-valid @enderror" name="role_id"
                        type="number" id="role_id" value="{{$cadastro->role_id??null}}">
                    @error('role_id')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>



            <div class="row mt-2">
                <div class="col-6">
                    <button class="btn btn-primary">Salvar</button>
                    <a href="{{route('TemFuncoes.index')}}" class="btn btn-secondary">Retornar para lista</a>
                </div>
            </div>
        </div>
    </div>



