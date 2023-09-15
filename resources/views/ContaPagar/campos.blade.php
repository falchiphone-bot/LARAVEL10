   @csrf
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <label for="nome">DESCRIÇÃO</label>
                    <input class="form-control @error('Descricao') is-invalid @else is-valid @enderror" name="Descricao"
                        type="text" id="Descricao" value="{{$contasPagar->Descricao??null}}">
                    @error('nome')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <label for="NumTitulo">TITULO</label>
                    <input class="form-control @error('NumTitulo') is-invalid @else is-valid @enderror" name="NumTitulo"
                        type="text" id="NumTitulo" value="{{$contasPagar->NumTitulo??null}}">
                    @error('nome')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <label for="Valor">VALOR</label>
                    <input class="form-control @error('Valor') is-invalid @else is-valid @enderror" name="Valor"
                        type="text" id="Valor" value="{{$contasPagar->Valor??null}}">
                        @error('Valor')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <label for="DataProgramacao">Data programação/contabilidade</label>
                    <input class="form-control @error('DataProgramacao') is-invalid @else is-valid @enderror" name="DataProgramacao"
                        type="date" id="DataProgramacao" value="{{$contasPagar->DataProgramacao??null}}">
                        @error('DataProgramacao')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <label for="DataVencimento">Data vencimento</label>
                    <input class="form-control @error('DataVencimento') is-invalid @else is-valid @enderror" name="DataVencimento"
                        type="date" id="DataVencimento" value="{{$contasPagar->DataVencimento??null}}">
                        @error('DataVencimento')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <label for="DataDocumento">Data documento</label>
                    <input class="form-control @error('DataDocumento') is-invalid @else is-valid @enderror" name="DataDocumento"
                        type="date" id="DataDocumento" value="{{$contasPagar->DataDocumento??null}}">
                        @error('DataDocumento')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-6">

                    <button class="btn btn-primary">Salvar</button>
                    <a href="{{route('ContasPagar.index')}}" class="btn btn-warning">Retornar para lista de contas</a>
                </div>
            </div>
        </div>
    </div>



