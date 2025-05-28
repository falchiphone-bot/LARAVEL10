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
            <label for="nomePia">Nome do Tópico para PIA</label>
            <input required
                class="form-control @error('nomePia') is-invalid @else is-valid @enderror"
                name="nomePia"
                type="text"
                id="nomePia"
                maxlength="250"
                value="{{ old('nomePia', $model->nomePia ?? '') }}"
                oninput="atualizarContador()"
            >

            <small id="contadorNomePia" class="form-text text-muted">
                0/250 caracteres
            </small>

            @error('nomePia')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>


        <div class="row mt-2">
            <div class="col-6">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{route('Irmaos_EmausPia.index')}}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>

<script>
function atualizarContador() {
    const input = document.getElementById('nomePia');
    if (!input) return; // Verifica se o elemento existe
    const contador = document.getElementById('contadorNomePia');
    contador.textContent = `${input.value.length}/250 caracteres`;
}
document.addEventListener('DOMContentLoaded', atualizarContador);
</script>
