@csrf
<div class="card">
    <div class="card-body">
        @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        {{ session(['success' =>  null ]) }}
        @elseif (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
        {{ session(['error' => NULL])}}
        @endif


        {{-- @dd($model ?? null) --}}
        <div class="form-group">
            <label for="nomeServico">Nome do serviço</label>
            <input required
                class="form-control @error('nomeServico') is-invalid @else is-valid @enderror"
                name="nomeServico"
                type="text"
                id="nomeServico"
                maxlength="250"
                value="{{ old('nomeServico', $model->nomeServico ?? '') }}"
                oninput="atualizarContador()"
            >

            <small id="contadorNomeServico" class="form-text text-muted">
                0/250 caracteres
            </small>

            @error('nomeServico')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>


        </div>
        <div class="row mt-2">
            <div class="col-6">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{route('Irmaos_EmausServicos.index')}}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>

<script>
function atualizarContador() {
    const input = document.getElementById('nomeServico');
    const contador = document.getElementById('contadorNomeServico');
    contador.textContent = `${input.value.length}/250 caracteres`;
}
document.addEventListener('DOMContentLoaded', atualizarContador);
</script>
