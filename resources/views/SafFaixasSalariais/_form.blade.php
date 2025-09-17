@php($editing = isset($model))
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nome</label>
        <input type="text" name="nome" value="{{ old('nome', $editing? $model->nome : '') }}" class="form-control @error('nome') is-invalid @enderror">
        @error('nome')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Função Profissional</label>
        <select name="funcao_profissional_id" class="form-select @error('funcao_profissional_id') is-invalid @enderror">
            <option value="">-- Selecione --</option>
            @foreach($funcoes as $id=>$nome)
                <option value="{{ $id }}" @selected(old('funcao_profissional_id', $editing? $model->funcao_profissional_id : null)==$id)>{{ $nome }}</option>
            @endforeach
        </select>
        @error('funcao_profissional_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Tipo de Prestador</label>
        <select name="saf_tipo_prestador_id" class="form-select @error('saf_tipo_prestador_id') is-invalid @enderror">
            <option value="">-- Selecione --</option>
            @foreach($tipos as $id=>$nome)
                <option value="{{ $id }}" @selected(old('saf_tipo_prestador_id', $editing? $model->saf_tipo_prestador_id : null)==$id)>{{ $nome }}</option>
            @endforeach
        </select>
        @error('saf_tipo_prestador_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Senioridade</label>
        <select name="senioridade" class="form-select @error('senioridade') is-invalid @enderror">
            <option value="">-- Opcional --</option>
            @foreach($senioridades as $opt)
                <option value="{{ $opt }}" @selected(old('senioridade', $editing? $model->senioridade : null)==$opt)>{{ $opt }}</option>
            @endforeach
        </select>
        @error('senioridade')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Tipo de Contrato</label>
        <select name="tipo_contrato" class="form-select @error('tipo_contrato') is-invalid @enderror" required>
            @foreach($tiposContrato as $opt)
                <option value="{{ $opt }}" @selected(old('tipo_contrato', $editing? $model->tipo_contrato : 'CLT')==$opt)>{{ $opt }}</option>
            @endforeach
        </select>
        @error('tipo_contrato')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Periodicidade</label>
        <select name="periodicidade" class="form-select @error('periodicidade') is-invalid @enderror" required>
            @foreach($periodicidades as $opt)
                <option value="{{ $opt }}" @selected(old('periodicidade', $editing? $model->periodicidade : 'MENSAL')==$opt)>{{ $opt }}</option>
            @endforeach
        </select>
        @error('periodicidade')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-2">
        <label class="form-label">Moeda</label>
        <input type="text" name="moeda" value="{{ old('moeda', $editing? $model->moeda : 'BRL') }}" class="form-control @error('moeda') is-invalid @enderror" maxlength="3">
        @error('moeda')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Valor mínimo</label>
        <input type="number" name="valor_minimo" value="{{ old('valor_minimo', $editing? $model->valor_minimo : '') }}" class="form-control @error('valor_minimo') is-invalid @enderror" step="0.0001" min="0" required>
        @error('valor_minimo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Valor máximo</label>
        <input type="number" name="valor_maximo" value="{{ old('valor_maximo', $editing? $model->valor_maximo : '') }}" class="form-control @error('valor_maximo') is-invalid @enderror" step="0.0001" min="0.0001" required>
        @error('valor_maximo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Vigência início</label>
        <input type="date" name="vigencia_inicio" value="{{ old('vigencia_inicio', $editing? optional($model->vigencia_inicio)->format('Y-m-d') : '') }}" class="form-control @error('vigencia_inicio') is-invalid @enderror" required>
        @error('vigencia_inicio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Vigência fim</label>
        <input type="date" name="vigencia_fim" value="{{ old('vigencia_fim', $editing && $model->vigencia_fim ? optional($model->vigencia_fim)->format('Y-m-d') : '') }}" class="form-control @error('vigencia_fim') is-invalid @enderror">
        @error('vigencia_fim')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-12">
        <label class="form-label">Observações</label>
        <textarea name="observacoes" class="form-control @error('observacoes') is-invalid @enderror" rows="3">{{ old('observacoes', $editing? $model->observacoes : '') }}</textarea>
        @error('observacoes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-12 form-check">
        <input class="form-check-input" type="checkbox" name="ativo" value="1" @checked(old('ativo', $editing? $model->ativo : true))>
        <label class="form-check-label">Ativo</label>
    </div>
</div>
@push('scripts')
<script>
    // Pré-visualização simples de moeda ao sair do campo (não altera o valor enviado)
    document.addEventListener('DOMContentLoaded', function() {
        const moeda = document.querySelector('input[name="moeda"]');
        const min = document.querySelector('input[name="valor_minimo"]');
        const max = document.querySelector('input[name="valor_maximo"]');
        function fmt(v) {
            const n = Number(v);
            if (Number.isNaN(n)) return '';
            return n.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        [min,max].forEach(el => {
            if (!el) return;
            el.addEventListener('blur', function(){
                const label = this.closest('.col-md-3')?.querySelector('label.form-label');
                const m = (moeda?.value || 'BRL').toUpperCase();
                if (label) label.innerText = label.innerText.split(' (')[0] + ' (' + (m==='BRL'?'R$ ':'') + fmt(this.value) + ')';
            });
        });
    });
</script>
@endpush
