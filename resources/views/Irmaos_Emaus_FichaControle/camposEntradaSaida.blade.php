@csrf
<div class="card shadow-sm rounded-3">
    <div class="card-body">

        {{-- @dd($errors) --}}

        {{-- Mensagens de sucesso ou erro --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @elseif (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Campo Nome na ficha de controle --}}
        <div class="form-group mb-3">
            <label for="Nome" class="form-label">Nome na ficha de controle: </label>
            <strong style="color: blue;">{{ old('Nome', isset($FichaControle->Nome) ? $FichaControle->Nome : '') }}</strong>
        </div>

        {{-- Campo Entrada --}}
        <div class="form-group mb-3">
            <label for="Entrada" class="form-label">Entrada</label>
            <input
                class="form-control @error('Entrada') is-invalid @enderror"
                name="Entrada"
                type="date"
                id="Entrada"
                value="{{ old('Entrada', isset($model->Entrada) ? $model->Entrada->format('Y-m-d') : '') }}"
            >
            @error('Entrada')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo Saida --}}
        <div class="form-group mb-3">
            <label for="Saida" class="form-label">Saída</label>
            <input
                class="form-control @error('Saida') is-invalid @enderror"
                name="Saida"
                type="date"
                id="Saida"
                value="{{ old('Saida', isset($model->Saida) ? $model->Saida->format('Y-m-d') : '') }}"
            >
            @error('Saida')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>


        {{-- Campo ANOTACAO --}}
            <div class="form-group mb-3">
            <label for="Anotacoes" class="form-label">ANOTAÇÕES</label>
            <textarea
                class="form-control @error('Anotacoes') is-invalid @enderror"
                name="Anotacoes"
                id="Anotacoes"
                rows="4"
            >{{ old('Anotacoes', isset($model->Anotacoes) ? $model->Anotacoes : '') }}</textarea>
            @error('Anotacoes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo ID do serviço --}}


        {{-- Botões --}}
        <div class="row mt-2">
            <div class="col-6">
                <button type="submit" class="btn btn-primary">Salvar entrada e saida</button>
                <a href="{{ route('Irmaos_EmausServicos.index') }}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>

