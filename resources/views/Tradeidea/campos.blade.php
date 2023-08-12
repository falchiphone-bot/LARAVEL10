@csrf
<div class="card">
    <div class="card-body">

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @elseif (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <label for="nome">Nome</label>
                <input required class="form-control @error('nome') is-invalid @else is-valid @enderror" name="nome"
                    type="text" id="nome" value="{{ $model->nome ?? null }}">
                @error('nome')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="email">Email</label>
                <input required class="form-control @error('email') is-invalid @else is-valid @enderror" name="email"
                    type="text" id="email" value="{{ $model->email ?? null }}">
                @error('email')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="telefone">Telefone</label>
                <input required class="form-control @error('telefone') is-invalid @else is-valid @enderror" name="telefone"
                    type="text" id="telefone" value="{{ $model->telefone ?? null }}">
                @error('telefone')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="licencacbf">Licença CBF</label>
                <input required class="form-control @error('licencaCBF') is-invalid @else is-valid @enderror" name="licencaCBF"
                    type="text" id="licencaCBF" value="{{ $model->licencaCBF ?? null }}">
                @error('licencaCBF')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-sm-6">
                <label for="CargoProfissional" style="color: black;">Cargo profissional</label>
                <select required class="form-control select2" id="CargoProfissional" name="CargoProfissional">
                    <option value="">
                        Selecionar cargo profissional
                    </option>
                    @foreach ($cargoprofissional as $cargoprofissionalitem)
                        <option @if ($cargoprofissional ?? null) @if ($model->CargoProfissional == $cargoprofissionalitem->id) selected @endif
                            @endif
                            value="{{ $cargoprofissionalitem->id }}">
                            {{ $cargoprofissionalitem->nome }}
                        </option>
                    @endforeach


                </select>
            </div>




            <div class="col-sm-6">
                <label for="FuncaoProfissional" style="color: black;">Função profissional</label>
                <select required class="form-control select2" id="FuncaoProfissional" name="FuncaoProfissional">
                    <option value="">
                        Selecionar função profissional
                    </option>
                    @foreach ($funcaoprofissional as $funcaoprofissionalitem)
                        <option @if ($funcaoprofissional ?? null) @if ($model->FuncaoProfissional == $funcaoprofissionalitem->id) selected @endif
                            @endif
                            value="{{ $funcaoprofissionalitem->id }}">
                            {{ $funcaoprofissionalitem->nome }}
                        </option>
                    @endforeach


                </select>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12 col-md-6">
            <button class="btn btn-primary btn-block">Salvar edição do registro</button>
        </div>
        <div class="col-12 col-md-6 mt-2 mt-md-0">
            <a href="{{ route('Preparadores.index') }}" class="btn btn-warning btn-block">Retornar para lista</a>
        </div>
    </div>

</form>
{{-- ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
 @include('Preparadores.arquivos')
 {{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}

</div>


