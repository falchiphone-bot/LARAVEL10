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



            <div class="col-6">
                <label for="nome">Nome</label>
                <input required class="form-control @error('nome') is-invalid @else is-valid @enderror" name="nome"
                    type="text" id="nome" value="{{ $model->nome ?? null }}">
                @error('nome')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-6">
                <label for="email">Email</label>
                <input required class="form-control @error('email') is-invalid @else is-valid @enderror" name="email"
                    type="text" id="email" value="{{ $model->email ?? null }}">
                @error('email')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-6">
                <label for="telefone">Telefone</label>
                <input required class="form-control @error('telefone') is-invalid @else is-valid @enderror" name="telefone"
                    type="text" id="telefone" value="{{ $model->telefone ?? null }}">
                @error('nome')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-6">
                <label for="licencacbf">Licença CBF</label>
                <input required class="form-control @error('licencaCBF') is-invalid @else is-valid @enderror" name="licencaCBF"
                    type="text" id="licencaCBF" value="{{ $model->licencaCBF ?? null }}">
                @error('nome')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>


        </div>
    </div>

    <div class="row mt-2">
        <div class="col-6">
            <button class="btn btn-primary">Salvar</button>
            <a href="{{ route('Preparadores.index') }}" class="btn btn-warning">Retornar para lista</a>
        </div>
    </div>
</div>
</div>
