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
       @can('IRMAOS_EMAUS_FICHA_CONTROLE - VER')
                                    <td>
                                        <a href="{{ route('Irmaos_Emaus_FichaControle.show', $idFichaControle) }}" class="btn btn-info" tabindex="-1"
                                            role="button" aria-disabled="true">Retornar para a ficha</a>
                                    </td>
                                @endcan
        </div>


        {{-- Botões para marcar ENTRADA ou SAÍDA --}}
<div class="card mb-3 shadow-sm" style="background-color: #0a6fcd;"> {{-- Fundo azul claro --}}

</div>

{{-- Campo TOPICO PIA--}}
        <div class="form-group mb-3">
            <label for="idIrmaos_EmausPia" class="form-label">Tópicos PIA</label>
            <select
                required
                class="form-control @error('idIrmaos_EmausPia') is-invalid @enderror"
                name="idIrmaos_EmausPia"
                id="idIrmaos_EmausPia"
            >
                <option value="">Selecione um TÓPICO</option>
                @foreach ($Irmaos_EmausPia as $id =>$servico)
                    <option {{ old('idServicos', $model->idServicos ?? '') == $id ? 'selected' : '' }}
                        value="{{ $id }}">
                        {{ $servico }}
                    </option>
                @endforeach
            </select>


            @error('idServicos')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo EntradaSaida --}}
        <div class="form-group mb-3">
            <label for="Data" class="form-label">Data</label>
            <input
                class="form-control @error('Entrada') is-invalid @enderror"
                name="Data"
                type="date"
                id="Data"
                value="{{ old('Data', isset($model->Data) ? $model->data->format('Y-m-d') : '') }}"
            >
            @error('Data')
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


        {{-- Botões --}}
        <div class="row mt-2">
            <div class="col-6">
                <button type="submit" class="btn btn-primary">Salvar entrada e saida</button>
                <a href="{{ route('Irmaos_Emaus_FichaControle.index') }}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>

