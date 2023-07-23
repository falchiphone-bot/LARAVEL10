   @csrf
                    <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
                    <a class="btn btn-danger" href="CentroCustos/dashboard">Incluir conta na empresa abaixo selecionada</a> </nav>

    <div class="card">
          <div class="card-body" style="background-color: rgb(244, 33, 33)">
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
                    <label for="Codigo">Código</label>
                    <input class="form-control @error('Codigo') is-invalid @else is-valid @enderror" name="Codigo"
                        type="text" id="Codigo" value="{{$cadastro->Codigo??null}}">
                        @error('Codigo')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <label for="Grau">Grau</label>
                    <input class="form-control @error('Grau') is-invalid @else is-valid @enderror" name="Grau"
                        type="text" id="Grau" value="{{$cadastro->Grau??null}}">
                        @error('Grau')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <label for="Tipo">Tipo S - Sintético ou A - Analítico</label>
                    <input class="form-control @error('Grau') is-invalid @else is-valid @enderror" name="Tipo"
                        type="text" id="Tipo" value="{{$cadastro->Tipo??null}}">
                        @error('Grau')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <div class="badge bg-info text-wrap" style="width: 100%; height: 50%; font-size: 24px;">
                    CLUBE
                </div>
                <select required class="form-control select2" id="EmpresaSelecionada" name="EmpresaSelecionada">
                    <option value="">Selecionar clube</option>
                    @foreach ($Empresas as $Empresa)
                    <option value="{{ $Empresa->ID }}">
                        {{ $Empresa->Descricao }}
                    </option>
                    @endforeach
                </select>

            </div>


            <div class="row mt-2">
                <div class="col-6">
                    <button class="btn btn-success">Salvar inclusão de conta na empresa acima selecionada</button>
                    {{-- <a href="{{route('PlanoContas.index')}}" class="btn btn-warning">Retornar para lista do plano de contas</a> --}}
                </div>
            </div>
        </div>
    </div>



