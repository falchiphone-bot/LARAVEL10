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

{{-- Campo Empresa --}}
<input
    type="hidden"
    name="Empresa"
    value="{{ old('Empresa', isset($FichaControle->Empresa) ? $FichaControle->Empresa : '') }}"
>

{{-- Campo id da ficha de controle --}}
<input
    type="hidden"
    name="idFichaControle"
    value="{{ old('idFichaControle', isset($FichaControle->id) ? $FichaControle->id : '') }}"
>



        {{-- Campo Nome na ficha de controle --}}
        <div class="form-group mb-3">
            <label for="Nome" class="form-label">Nome na ficha de controle: </label>
            <strong style="color: blue;">{{ old('Nome', isset($FichaControle->Nome) ? $FichaControle->Nome : '') }}</strong>
        </div>


        {{-- Botões para marcar ENTRADA ou SAÍDA --}}
<div class="card mb-3 shadow-sm" style="background-color: #0a6fcd;"> {{-- Fundo azul claro --}}
    <div class="card-body">
        <div class="form-group">
            <label class="form-label">Tipo de Registro:</label>
            <div>
                <div class="form-check form-check-inline">
                    <input
                        class="form-check-input"
                        type="radio"
                        name="TipoEntradaSaida"
                        id="tipoEntrada"
                        value="entrada"
                        {{ old('TipoEntradaSaida') == 'entrada' ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="tipoEntrada">Entrada</label>
                </div>
                <div class="form-check form-check-inline">
                    <input
                        class="form-check-input"
                        type="radio"
                        name="TipoEntradaSaida"
                        id="tipoSaida"
                        value="saida"
                        {{ old('TipoEntradaSaida') == 'saida' ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="tipoSaida">Saída</label>
                </div>
            </div>
            @error('TipoEntradaSaida')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>


        {{-- Campo EntradaSaida --}}
        <div class="form-group mb-3">
            <label for="Entrada" class="form-label">Data de Entrada ou Saida</label>
            <input
                class="form-control @error('Entrada') is-invalid @enderror"
                name="DataEntradaSaida"
                type="date"
                id="DataEntradaSaida"
                value="{{ old('DataEntradaSaida', isset($model->DataEntradaSaida) ? $model->Entrada->format('Y-m-d') : '') }}"
            >
            @error('DataEntradaSaida')
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

