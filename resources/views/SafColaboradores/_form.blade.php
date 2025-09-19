@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nome</label>
        <input type="text" name="nome" class="form-control" value="{{ old('nome', $model->nome ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Representante</label>
        <select name="representante_id" class="form-select">
            <option value="">-- selecione --</option>
            @foreach($representantes as $id => $nome)
                <option value="{{ $id }}" {{ (string)old('representante_id', $model->representante_id ?? '') === (string)$id ? 'selected' : '' }}>{{ $nome }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Função Profissional</label>
        <select name="funcao_profissional_id" class="form-select">
            <option value="">-- selecione --</option>
            @foreach($funcoes as $id => $nome)
                <option value="{{ $id }}" {{ (string)old('funcao_profissional_id', $model->funcao_profissional_id ?? '') === (string)$id ? 'selected' : '' }}>{{ $nome }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Tipo de Colaborador</label>
        <select name="saf_tipo_prestador_id" class="form-select">
            <option value="">-- selecione --</option>
            @foreach($tipos as $id => $nome)
                <option value="{{ $id }}" {{ (string)old('saf_tipo_prestador_id', $model->saf_tipo_prestador_id ?? '') === (string)$id ? 'selected' : '' }}>{{ $nome }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Faixa Salarial</label>
        <select name="saf_faixa_salarial_id" class="form-select">
            <option value="">-- selecione --</option>
            @foreach($faixas as $id => $nome)
                <option value="{{ $id }}" {{ (string)old('saf_faixa_salarial_id', $model->saf_faixa_salarial_id ?? '') === (string)$id ? 'selected' : '' }}>{{ $nome }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Chave PIX</label>
        <select name="pix_nome" class="form-select">
            <option value="">-- selecione --</option>
            @foreach(($pixList ?? []) as $nome => $label)
                <option value="{{ $nome }}" {{ (string)old('pix_nome', $model->pix_nome ?? '') === (string)$nome ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <div class="form-text">Cadastre as chaves em: Dashboard → PIX.</div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Forma de Pagamento</label>
        <select name="forma_pagamento_nome" class="form-select">
            <option value="">-- selecione --</option>
            @foreach(($formasPagamento ?? []) as $nome => $label)
                <option value="{{ $nome }}" {{ (string)old('forma_pagamento_nome', $model->forma_pagamento_nome ?? '') === (string)$nome ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <div class="form-text">Cadastre as formas em: Dashboard → Formas de Pagamento.</div>
    </div>
    <div class="col-md-3">
        <label class="form-label">Valor de salário</label>
        <input type="text" name="valor_salario" class="form-control money-br" value="{{ old('valor_salario', isset($model->valor_salario) ? number_format($model->valor_salario, 2, ',', '.') : '') }}" placeholder="0,00">
        <div class="form-text">Use vírgula como separador decimal. Ex: 1.234,56</div>
    </div>
    <div class="col-md-3">
        <label class="form-label">Dia do pagamento</label>
        <select name="dia_pagamento" class="form-select">
            <option value="">-- selecione --</option>
            @for($d=1;$d<=31;$d++)
                <option value="{{ $d }}" {{ (string)old('dia_pagamento', $model->dia_pagamento ?? '') === (string)$d ? 'selected' : '' }}>{{ str_pad((string)$d,2,'0',STR_PAD_LEFT) }}</option>
            @endfor
        </select>
        <div class="form-text">Dia do mês (01 a 31). Opcional.</div>
    </div>
    <div class="col-md-3">
        <label class="form-label">Documento</label>
        <input type="text" name="documento" class="form-control" value="{{ old('documento', $model->documento ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">CPF</label>
        <input type="text" name="cpf" class="form-control cpf-mask" value="{{ old('cpf', $model->cpf ?? '') }}" placeholder="000.000.000-00" maxlength="14" pattern="^\d{3}\.\d{3}\.\d{3}-\d{2}$|^\d{11}$">
        <div class="form-text">Digite apenas números ou no formato 000.000.000-00.</div>
    </div>
    <div class="col-md-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $model->email ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Telefone</label>
        <input type="text" name="telefone" class="form-control" value="{{ old('telefone', $model->telefone ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Cidade</label>
        <input type="text" name="cidade" class="form-control" value="{{ old('cidade', $model->cidade ?? '') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">UF</label>
        <input type="text" name="uf" class="form-control" maxlength="2" value="{{ old('uf', $model->uf ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">País</label>
        <input type="text" name="pais" class="form-control" value="{{ old('pais', $model->pais ?? '') }}">
    </div>
    <div class="col-md-2 form-check mt-4">
        <input type="checkbox" name="ativo" value="1" class="form-check-input" id="ativoCheck" {{ old('ativo', $model->ativo ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="ativoCheck">Ativo</label>
    </div>
    <div class="col-12">
        <label class="form-label">Observações</label>
        <textarea name="observacoes" rows="3" class="form-control">{{ old('observacoes', $model->observacoes ?? '') }}</textarea>
    </div>
    <div class="col-12">
        <button class="btn btn-primary">Salvar</button>
        <a href="{{ route('SafColaboradores.index') }}" class="btn btn-secondary">Cancelar</a>
    </div>
    @if ($errors->any())
        <div class="col-12">
            <div class="alert alert-danger mt-2">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // máscara simples para moeda BRL (somente formatação visual, validação no backend)
    function maskMoneyBR(v){
        v = (v||'').toString();
        // remove tudo exceto dígitos
        v = v.replace(/\D/g,'');
        if (!v) return '';
        // garante pelo menos 3 dígitos para posicionar a vírgula
        while (v.length < 3) v = '0' + v;
        const cents = v.slice(-2);
        let ints = v.slice(0, -2);
        // adiciona pontos de milhar
        ints = ints.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return ints + ',' + cents;
    }

    function maskCPF(value){
        const v = (value||'').replace(/\D/g,'').slice(0,11);
        const p1 = v.slice(0,3);
        const p2 = v.slice(3,6);
        const p3 = v.slice(6,9);
        const p4 = v.slice(9,11);
        let out = '';
        if (p1) out = p1;
        if (p2) out += (out?'.':'') + p2;
        if (p3) out += (out?'.':'') + p3;
        if (p4) out += '-' + p4;
        return out;
    }
    document.querySelectorAll('input.cpf-mask').forEach(function(el){
        el.addEventListener('input', function(){ this.value = maskCPF(this.value); });
        // aplica na carga
        el.value = maskCPF(el.value);
    });

    document.querySelectorAll('input.money-br').forEach(function(el){
        // aplica máscara durante digitação
        el.addEventListener('input', function(){ this.value = maskMoneyBR(this.value); });
        // normaliza valor existente
        el.value = maskMoneyBR(el.value);
    });
});
</script>
@endpush
