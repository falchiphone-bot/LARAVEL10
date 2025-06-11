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

        <div class="row mt-2">
            <div class="col-6">
                 <a href="{{ route('Irmaos_Emaus_FichaControle.index') }}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
        {{-- Campo Tipo de serviço--}}
        <div class="form-group mb-3">
            <label for="idServicos" class="form-label">Serviço</label>
            <select
                required
                class="form-control @error('idServicos') is-invalid @enderror"
                name="idServicos"
                id="idServicos"
            >
                <option value="">Selecione um serviço</option>
                @foreach ($Irmaos_EmausServicos as $id =>$servico)
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

        {{-- Campo Nome na ficha de controle --}}
        <div class="form-group mb-3">
            <label for="Nome" class="form-label">Nome na ficha de controle</label>
            <input
                required
                class="form-control @error('Nome') is-invalid @enderror"
                name="Nome"
                type="text"
                id="Nome"
                maxlength="250"
                value="{{ old('Nome', $model->Nome ?? '') }}"
                oninput="atualizarContador()"
            >
            <small id="contadorNomeServico" class="form-text text-muted">
                0/250 caracteres
            </small>
            @error('Nome')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo Data de Nascimento --}}
        <div class="form-group mb-3">
            <label for="Nascimento" class="form-label">Data de nascimento</label>
            <input
                required
                class="form-control @error('Nascimento') is-invalid @enderror"
                name="Nascimento"
                type="date"
                id="Nascimento"
                value="{{ old('Nascimento', isset($model->Nascimento) ? $model->Nascimento->format('Y-m-d') : '') }}"
            >
            @error('Nascimento')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo CidadeNaturalidade --}}
        <div class="form-group mb-3">
            <label for="CidadeNaturalidade" class="form-label">Cidade Naturalidade (Onde nasceu)</label>
            <input
                required
                class="form-control @error('CidadeNaturalidade') is-invalid @enderror"
                name="CidadeNaturalidade"
                type="text"
                id="CidadeNaturalidade"
                value="{{ old('CidadeNaturalidade', $model->CidadeNaturalidade ?? '') }}"
            >
            @error('CidadeNaturalidade')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo UF_Naturalidade --}}
        <div class="form-group mb-3">
            <label for="UF_Naturalidade" class="form-label">Unidade Federativa da Naturalidade (Estado)</label>
            <input
                required
                class="form-control @error('UF_Naturalidade') is-invalid @enderror"
                name="UF_Naturalidade"
                type="text"
                id="UF_Naturalidade"
                value="{{ old('UF_Naturalidade', $model->UF_Naturalidade ?? '') }}"
            >
            @error('UF_Naturalidade')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo Mae --}}
        <div class="form-group mb-3">
            <label for="Mae" class="form-label">Mãe</label>
            <input
                required
                class="form-control @error('Mae') is-invalid @enderror"
                name="Mae"
                type="text"
                id="Mae"
                value="{{ old('Mae', $model->Mae ?? '') }}"
            >
            @error('Mae')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo Pai --}}
        <div class="form-group mb-3">
            <label for="Pai" class="form-label">Pai</label>
            <input
                class="form-control @error('Pai') is-invalid @enderror"
                name="Pai"
                type="text"
                id="Pai"
                value="{{ old('Pai', $model->Pai ?? '') }}"
            >
            @error('Pai')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo Rg --}}
        <div class="form-group mb-3">
            <label for="Rg" class="form-label">Rg</label>
            <input
                class="form-control @error('Rg') is-invalid @enderror"
                name="Rg"
                type="text"
                id="Rg"
                value="{{ old('Rg', $model->Rg ?? '') }}"
            >
            @error('Rg')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo Cpf --}}
        <div class="form-group mb-3">
            <label for="Cpf" class="form-label">Cpf</label>
            <input
                class="form-control @error('Cpf') is-invalid @enderror"
                name="Cpf"
                type="text"
                id="Cpf"
                value="{{ old('Cpf', $model->Cpf ?? '') }}"
            >
            @error('Cpf')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo Nis --}}
        <div class="form-group mb-3">
            <label for="Nis" class="form-label">Nis</label>
            <input
                class="form-control @error('Nis') is-invalid @enderror"
                name="Nis"
                type="text"
                id="Nis"
                value="{{ old('Nis', $model->Nis ?? '') }}"
            >
            @error('Nis')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo Escolaridade --}}
        <div class="form-group mb-3">
            <label for="Escolaridade" class="form-label">Escolaridade</label>
            <input
                class="form-control @error('Escolaridade') is-invalid @enderror"
                name="Escolaridade"
                type="text"
                id="Escolaridade"
                value="{{ old('Escolaridade', $model->Escolaridade ?? '') }}"
            >
            @error('Escolaridade')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo EntradaPrimeiraVez --}}
        <div class="form-group mb-3">
            <label for="EntradaPrimeiraVez" class="form-label">Entrada da Primeira Vez</label>
            <input
                class="form-control @error('EntradaPrimeiraVez') is-invalid @enderror"
                name="EntradaPrimeiraVez"
                type="date"
                id="EntradaPrimeiraVez"
                value="{{ old('EntradaPrimeiraVez', isset($model->EntradaPrimeiraVez) ? $model->EntradaPrimeiraVez->format('Y-m-d') : '') }}"
            >
            @error('EntradaPrimeiraVez')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo SaidaPrimeiraVez --}}
        <div class="form-group mb-3">
            <label for="SaidaPrimeiraVez" class="form-label">Saída da Primeira Vez</label>
            <input
                class="form-control @error('SaidaPrimeiraVez') is-invalid @enderror"
                name="SaidaPrimeiraVez"
                type="date"
                id="SaidaPrimeiraVez"
                value="{{ old('SaidaPrimeiraVez', isset($model->SaidaPrimeiraVez) ? $model->SaidaPrimeiraVez->format('Y-m-d') : '') }}"
            >
            @error('SaidaPrimeiraVez')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo Prontuario --}}
        <div class="form-group mb-3">
            <label for="Prontuario" class="form-label">Prontuário</label>
            <input
                class="form-control @error('Prontuario') is-invalid @enderror"
                name="Prontuario"
                type="number"
                id="Prontuario"
                value="{{ old('Prontuario', $model->Prontuario ?? '') }}"
                step="1"
                inputmode="numeric"
                pattern="\d*"
                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
            >
            @error('Prontuario')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo Livro --}}
        <div class="form-group mb-3">
            <label for="Livro" class="form-label">Livro</label>
            <input
                class="form-control @error('Livro') is-invalid @enderror"
                name="Livro"
                type="number"
                id="Livro"
                value="{{ old('Livro', $model->Livro ?? '') }}"
                step="1"
                inputmode="numeric"
                pattern="\d*"
                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
            >
            @error('Livro')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo Folha --}}
        <div class="form-group mb-3">
            <label for="Folha" class="form-label">Folha</label>
            <input
                class="form-control @error('Folha') is-invalid @enderror"
                name="Folha"
                type="number"
                id="Folha"
                value="{{ old('Folha', $model->Folha ?? '') }}"
                step="1"
                inputmode="numeric"
                pattern="\d*"
                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
            >
            @error('Folha')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>


        <div class="card-body bg-light">

         {{-- Campo contatos na ficha de controle --}}
        <div class="form-group mb-3">
            <label for="contatos" class="form-label">Contatos/familiares na ficha de controle</label>
            <input
                required
                class="form-control @error('contatos') is-invalid @enderror"
                name="contatos"
                type="text"
                id="contatos"
                maxlength="250"
                value="{{ old('contatos', $model->contatos ?? '') }}"
                oninput="atualizarContador()"
            >
            <small id="contadorNomeServico" class="form-text text-muted">
                0/250 caracteres
            </small>
            @error('contatos')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo endereço na ficha de controle --}}
        <div class="form-group mb-3">
            <label for="endereco" class="form-label">Endereço - ficha de controle</label>
            <input
                required
                class="form-control @error('endereco') is-invalid @enderror"
                name="endereco"
                type="text"
                id="endereco"
                maxlength="500"
                value="{{ old('endereco', $model->endereco ?? '') }}"
                oninput="atualizarContador()"
            >
            <small id="contadorNomeServico" class="form-text text-muted">
                0/500 caracteres
            </small>
            @error('endereco')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>


        {{-- Campo profissao na ficha de controle --}}
        <div class="form-group mb-3">
            <label for="profissao" class="form-label">Profissão - ficha de controle</label>
            <input
                required
                class="form-control @error('profissao') is-invalid @enderror"
                name="profissao"
                type="text"
                id="profissao"
                maxlength="500"
                value="{{ old('profissao', $model->profissao ?? '') }}"
                oninput="atualizarContador()"
            >
            <small id="contadorNomeServico" class="form-text text-muted">
                0/50 caracteres
            </small>
            @error('profissao')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo beneficios na ficha de controle --}}
        <div class="form-group mb-3">
            <label for="beneficios" class="form-label">Beneficios - ficha de controle</label>
            <input
                required
                class="form-control @error('beneficios') is-invalid @enderror"
                name="beneficios"
                type="text"
                id="beneficios"
                maxlength="500"
                value="{{ old('beneficios', $model->beneficios ?? '') }}"
                oninput="atualizarContador()"
            >
            <small id="contadorNomeServico" class="form-text text-muted">
                0/250 caracteres
            </small>
            @error('beneficios')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo observacoes --}}
            <div class="form-group mb-3">
            <label for="observacoes" class="form-label">Observações/anotações</label>
            <textarea
                class="form-control @error('observacoes') is-invalid @enderror"
                name="observacoes"
                id="observacoes"
                rows="10"
            >{{ old('observacoes', isset($model->observacoes) ? $model->observacoes : '') }}</textarea>
            @error('observacoes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

        {{-- Botões --}}
        <div class="row mt-2">
            <div class="col-6">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="{{ route('Irmaos_Emaus_FichaControle.index') }}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>

    </div>
</div>

<script>
function atualizarContador() {
    const input = document.getElementById('Nome');
    const contador = document.getElementById('contadorNomeServico');
    contador.textContent = `${input.value.length}/250 caracteres`;
}
document.addEventListener('DOMContentLoaded', atualizarContador);
</script>
