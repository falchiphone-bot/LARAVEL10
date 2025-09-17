@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nome</label>
        <input type="text" name="nome" class="form-control" value="{{ old('nome', $model->nome ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Cidade</label>
        <input type="text" name="cidade" class="form-control" value="{{ old('cidade', $model->cidade ?? '') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">UF</label>
        <input type="text" name="uf" class="form-control" maxlength="2" value="{{ old('uf', $model->uf ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">País</label>
        <input type="text" name="pais" class="form-control" value="{{ old('pais', $model->pais ?? 'BRASIL') }}">
    </div>
    <div class="col-md-8">
        <label class="form-label">Categorias</label>
        <select name="categorias[]" class="form-select" multiple size="6">
            @php
                $selecionadas = old('categorias', isset($model) ? $model->categorias->pluck('id')->all() : []);
            @endphp
            @foreach($categorias as $cat)
                <option value="{{ $cat->id }}" {{ in_array($cat->id, $selecionadas ?? []) ? 'selected' : '' }}>{{ $cat->nome }}</option>
            @endforeach
        </select>
        <small class="text-muted">Segure Ctrl (Windows) ou Command (Mac) para selecionar múltiplas.</small>
    </div>

    <div class="col-md-4">
        <label class="form-label">Federação</label>
        <select name="federacao_id" class="form-select">
            <option value="">— Selecione —</option>
            @php
                $selFederacao = old('federacao_id', $model->federacao_id ?? null);
            @endphp
            @isset($federacoes)
                @foreach($federacoes as $fed)
                    <option value="{{ $fed->id }}" {{ (string)$selFederacao === (string)$fed->id ? 'selected' : '' }}>{{ $fed->nome }}</option>
                @endforeach
            @endisset
        </select>
        <small class="text-muted">Vincule o campeonato a uma federação (opcional).</small>
        <div class="mt-2">
            @can('SAF_FEDERACOES - INCLUIR')
                <a class="btn btn-sm btn-outline-primary" href="{{ route('SafFederacoes.create') }}" target="_blank">Nova Federação</a>
            @endcan
            @can('SAF_FEDERACOES - LISTAR')
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('SafFederacoes.index') }}" target="_blank">Gerenciar Federações</a>
            @endcan
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary" type="submit">Salvar</button>
    <a href="{{ route('SafCampeonatos.index') }}" class="btn btn-warning">Retornar para lista</a>
    <a href="{{ route('Cadastros') }}" class="btn btn-secondary">Retornar ao menu de cadastros</a>
</div>
