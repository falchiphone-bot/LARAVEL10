   @csrf
    <div class="card">
        <div class="card-body" style="background-color: green">
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
                    <label for="cnpj">CNPJ</label>
                    <input class="form-control @error('Cnpj') is-invalid @else is-valid @enderror" name="Cnpj"
                        type="text" id="Cnpj" value="{{$cadastro->Cnpj??null}}">
                        @error('Cnpj')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <label for="ie">INSCRIÇÃO ESTADUAL</label>
                    <input class="form-control" name="Ie"
                        type="text" id="Ie" value="{{$cadastro->Ie??null}}">
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-check">
                        <label class="form-check-label" for="flexCheckDefault">
                          BLOQUEADA
                        </label>
                        <input type="hidden" name="Bloqueio" value="0">
                        <input class="form-check-input" name="Bloqueio" type="checkbox" @if($cadastro->Bloqueio) checked @endif value="1" id="flexCheckDefault">
                    </div>
                </div>
            </div>



            <div class="row mt-2">
                <div class="col-6">
                    <button class="btn btn-primary">Salvar</button>
                    <a href="{{route('Empresas.index')}}" class="btn btn-warning">Retornar para lista de empresas</a>
                </div>
            </div>
        </div>
    </div>



